<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\Card;
use App\Models\Racer;
use App\Models\Team;
use App\Models\TournamentParticipant;
use App\Helpers\AblyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RaceController extends Controller
{
    /**
     * Display a listing of races in the active tournament.
     */
    public function index(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Determine view mode
        $viewMode = $request->get('view', 'team'); // team | with_racer

        // Build query for races
        $query = Race::where('tournament_id', $tournament->id)
            ->with(['racer.team', 'team', 'card']);

        // Filter by stage if provided
        if ($request->has('stage') && $request->stage !== '') {
            $query->where('stage', $request->stage);
        }

        // Filter by track if provided
        if ($request->has('track') && $request->track !== '') {
            $query->where('track', $request->track);
        }

        // Filter by team if provided
        if ($request->has('team_id') && $request->team_id) {
            $query->where('team_id', $request->team_id);
        }

        // Get all stages for filter dropdown
        $stages = Race::where('tournament_id', $tournament->id)
            ->distinct()
            ->orderBy('stage', 'desc')
            ->pluck('stage');

        // Get all tracks for filter dropdown
        $tracks = Race::where('tournament_id', $tournament->id)
            ->distinct()
            ->orderBy('track')
            ->pluck('track');

        // Get teams in the active tournament for filter
        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');
        $teams = Team::whereIn('id', $teamIds)->orderBy('team_name')->get();

        // Get cards in the active tournament for modal
        $racerIds = Racer::whereIn('team_id', $teamIds)->pluck('id');
        $cards = Card::whereIn('racer_id', $racerIds)
            ->where('status', 'ACTIVE')
            ->with(['racer.team'])
            ->orderBy('card_code')
            ->get();

        // Get all races (no pagination for grid view)
        $allRaces = $query->orderBy('stage', 'desc')
            ->orderBy('race_no')
            ->orderBy('track')
            ->orderBy('lane')
            ->get();

        // Organize races by stage and race_no for grid display
        $racesByStage = [];
        foreach ($allRaces as $race) {
            $stage = $race->stage;
            $raceNo = $race->race_no ?? 0;

            if (!isset($racesByStage[$stage])) {
                $racesByStage[$stage] = [];
            }

            if (!isset($racesByStage[$stage][$raceNo])) {
                $racesByStage[$stage][$raceNo] = [];
            }

            // Store race by lane (A, B, C, D, E, F, etc.)
            $racesByStage[$stage][$raceNo][$race->lane] = $race;
        }

        // Get the selected stage or use the first available stage
        $selectedStage = $request->has('stage') && $request->stage !== ''
            ? (int) $request->stage
            : ($stages->first() ?? null);

        // Get race numbers for the selected stage
        $raceNumbers = [];
        if ($selectedStage && isset($racesByStage[$selectedStage])) {
            $raceNumbers = array_keys($racesByStage[$selectedStage]);
            sort($raceNumbers);
        }

        // Calculate max race number to display (show at least 12 rows)
        $maxRaceNo = max(12, $raceNumbers ? max($raceNumbers) : 0);

        return view('races.index', compact(
            'racesByStage',
            'tournament',
            'stages',
            'tracks',
            'teams',
            'selectedStage',
            'raceNumbers',
            'maxRaceNo',
            'viewMode',
            'cards'
        ));
    }

    /**
     * Show the form for creating a new race.
     */
    public function create(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Get stage from query parameter (if filtering by stage)
        $selectedStage = $request->get('stage');

        // Get cards in the active tournament
        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');

        $racerIds = Racer::whereIn('team_id', $teamIds)->pluck('id');

        $cards = Card::whereIn('racer_id', $racerIds)
            ->where('status', 'ACTIVE')
            ->with(['racer.team'])
            ->orderBy('card_code')
            ->get();

        return view('races.create', compact('tournament', 'cards', 'selectedStage'));
    }

    /**
     * Store a newly created race.
     */
    public function store(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        $validated = $request->validate([
            'card_id' => 'required|uuid|exists:cards,id',
            'stage' => 'nullable|integer|min:1',
        ]);

        // Get card and verify it belongs to tournament
        $card = Card::with('racer.team')->findOrFail($validated['card_id']);

        // Verify card's racer belongs to active tournament
        if (!$card->racer_id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Card is not assigned to any racer.');
        }

        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');

        $isValidRacer = Racer::whereIn('team_id', $teamIds)
            ->where('id', $card->racer_id)
            ->exists();

        if (!$isValidRacer) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Card does not belong to the active tournament.');
        }

        // Get racer and team
        $racer = $card->racer;
        $team = $racer->team;

        if (!$team) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Racer is not assigned to any team.');
        }

        // Calculate stage
        // If stage is provided in request, use it; otherwise use current_stage + 1
        $stage = $validated['stage'] ?? ($tournament->current_stage + 1);

        // Calculate track and lane
        $trackAndLane = $this->calculateTrackAndLane($tournament, $stage);

        // Check if stage + race_no + lane combination already exists
        $existingRace = Race::where('tournament_id', $tournament->id)
            ->where('stage', $stage)
            ->where('race_no', $trackAndLane['race_no'])
            ->where('lane', $trackAndLane['lane'])
            ->first();

        if ($existingRace) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A race already exists for Stage ' . $stage . ', Race No ' . $trackAndLane['race_no'] . ', Lane ' . $trackAndLane['lane'] . '.');
        }

        // Create race
        $race = Race::create([
            'id' => Str::uuid(),
            'tournament_id' => $tournament->id,
            'stage' => $stage,
            'race_no' => $trackAndLane['race_no'],
            'track' => $trackAndLane['track'],
            'lane' => $trackAndLane['lane'],
            'racer_id' => $racer->id,
            'team_id' => $team->id,
            'card_id' => $card->id,
            'race_time' => null, // TODO: Set race time later
            'created_by' => auth()->id(),
        ]);

        // Publish best race update
        // $this->publishBestRaceUpdate($tournament);

        return redirect()->route('tournament.races.index')
            ->with('success', "Race created successfully. Stage: {$stage}, Race No: {$trackAndLane['race_no']}, Track: {$trackAndLane['track']}, Lane: {$trackAndLane['lane']}");
    }

    /**
     * Calculate race_no, track and lane for a new race in a specific stage.
     * 
     * @param \App\Models\Tournament $tournament
     * @param int $stage
     * @return array ['race_no' => int, 'track' => int, 'lane' => string]
     */
    private function calculateTrackAndLane($tournament, $stage)
    {
        // Get the number of races already in this stage
        $racesInStage = Race::where('tournament_id', $tournament->id)
            ->where('stage', $stage)
            ->count();

        // Race index (0-based)
        $raceIndex = $racesInStage;

        // Maximum number of lanes = track_number * 3
        // Each track has 3 lanes
        $maxLanes = $tournament->track_number * 3;

        // Calculate race_no (1-based)
        // race_no = floor(raceIndex / maxLanes) + 1
        // First maxLanes races have race_no = 1, next maxLanes have race_no = 2, etc.
        $raceNo = floor($raceIndex / $maxLanes) + 1;

        // Calculate lane index (0-based, cycles within max lanes)
        // This ensures we don't exceed the maximum lanes available
        $laneIndex = $raceIndex % $maxLanes;

        // Calculate track number (1-based)
        // Track = floor(laneIndex / 3) + 1
        // This ensures track doesn't exceed tournament->track_number
        $track = floor($laneIndex / 3) + 1;

        // Convert lane index to letter (A, B, C, D, E, F, ...)
        $lane = chr(65 + $laneIndex); // 65 is ASCII for 'A'

        return [
            'race_no' => $raceNo,
            'track' => (string) $track,
            'lane' => $lane,
        ];
    }

    /**
     * Toggle the is_called status for all races in a specific race_no.
     */
    public function toggleCalled(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return response()->json(['error' => 'No active tournament'], 400);
        }

        $validated = $request->validate([
            'stage' => 'required|integer',
            'race_no' => 'required|integer',
            'is_called' => 'required|boolean',
        ]);

        // Update all races for this race_no in the stage
        Race::where('tournament_id', $tournament->id)
            ->where('stage', $validated['stage'])
            ->where('race_no', $validated['race_no'])
            ->update(['is_called' => $validated['is_called']]);

        return response()->json(['success' => true]);
    }

    /**
     * Publish best race update to Ably
     */
    private function publishBestRaceUpdate($tournament)
    {
        $nextStage = $tournament->current_stage + 1;
        
        // Top 6 Teams with most races in next stage
        $topTeams = Race::where('tournament_id', $tournament->id)
            ->where('stage', $nextStage)
            ->join('teams', 'races.team_id', '=', 'teams.id')
            ->select('teams.team_name', DB::raw('count(*) as total'))
            ->groupBy('teams.id', 'teams.team_name')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(function ($race) {
                return [
                    'TEAM NAME' => $race->team_name,
                    'TOTAL' => $race->total
                ];
            })
            ->toArray();
        
        // Publish to Ably
        AblyHelper::publishBestRace($tournament, $topTeams);
    }

    /**
     * Balance races by ensuring the last race has at least 2 lanes per track.
     * Moves teams from the previous race if needed.
     */
    public function balanceRaces(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return response()->json(['error' => 'Please select a tournament first.'], 400);
        }

        $validated = $request->validate([
            'stage' => 'required|integer|min:1',
        ]);

        $stage = $validated['stage'];

        DB::beginTransaction();
        try {
            // Get all races in the stage, ordered by race_no and lane
            $races = Race::where('tournament_id', $tournament->id)
                ->where('stage', $stage)
                ->orderBy('race_no')
                ->orderBy('lane')
                ->get();

            if ($races->isEmpty()) {
                return response()->json(['error' => 'No races found in stage ' . $stage], 404);
            }

            // Group races by race_no
            $racesByRaceNo = [];
            foreach ($races as $race) {
                $raceNo = $race->race_no ?? 0;
                if (!isset($racesByRaceNo[$raceNo])) {
                    $racesByRaceNo[$raceNo] = [];
                }
                $racesByRaceNo[$raceNo][] = $race;
            }

            // Sort race numbers
            ksort($racesByRaceNo);
            $raceNumbers = array_keys($racesByRaceNo);

            if (count($raceNumbers) < 2) {
                return response()->json(['error' => 'At least 2 races are required to balance.'], 400);
            }

            // Get the last race number
            $lastRaceNo = end($raceNumbers);
            $previousRaceNo = prev($raceNumbers);

            \Log::info('Race balancing - Stage ' . $stage . ' - Last race: ' . $lastRaceNo . ', Previous race: ' . $previousRaceNo);

            // Get races for last and previous race
            $lastRaceRaces = $racesByRaceNo[$lastRaceNo] ?? [];
            $previousRaceRaces = $racesByRaceNo[$previousRaceNo] ?? [];

            if (empty($lastRaceRaces) || empty($previousRaceRaces)) {
                return response()->json(['error' => 'Invalid race data.'], 400);
            }

            // Log current state of last race
            $lastRaceDetails = [];
            foreach ($lastRaceRaces as $race) {
                $lastRaceDetails[] = [
                    'lane' => $race->lane,
                    'track' => $race->track,
                    'team' => $race->team->team_name ?? 'Unknown'
                ];
            }
            \Log::info('Race balancing - Last race (' . $lastRaceNo . ') current state:', $lastRaceDetails);

            // Group last race by track to check which tracks need balancing
            $lastRaceByTrack = [];
            foreach ($lastRaceRaces as $race) {
                $track = (int) $race->track;
                if (!isset($lastRaceByTrack[$track])) {
                    $lastRaceByTrack[$track] = [];
                }
                $lastRaceByTrack[$track][] = $race;
            }

            // Check which tracks have only 1 lane (need balancing)
            $tracksNeedingBalance = [];
            $tracksWithExtraLanes = [];
            for ($track = 1; $track <= $tournament->track_number; $track++) {
                $laneCount = isset($lastRaceByTrack[$track]) ? count($lastRaceByTrack[$track]) : 0;
                if ($laneCount === 1) {
                    $tracksNeedingBalance[] = $track;
                } elseif ($laneCount >= 3) {
                    $tracksWithExtraLanes[] = $track;
                }
            }

            if (empty($tracksNeedingBalance)) {
                return response()->json(['info' => 'Races are already balanced.'], 200);
            }

            \Log::info('Race balancing - Tracks needing balance: ' . implode(', ', $tracksNeedingBalance));
            \Log::info('Race balancing - Tracks with extra lanes: ' . implode(', ', $tracksWithExtraLanes));

            // If we have tracks with extra lanes, balance within the last race
            // Otherwise, get teams from previous race
            $usePreviousRace = empty($tracksWithExtraLanes);

            if ($usePreviousRace) {
                // Check if previous race has enough teams to move
                if (count($previousRaceRaces) < count($tracksNeedingBalance)) {
                    return response()->json(['error' => 'Previous race does not have enough teams to balance.'], 400);
                }
                \Log::info('Race balancing - Using previous race for balancing');
            } else {
                \Log::info('Race balancing - Balancing within last race');
            }

            // Calculate the last lane based on tournament track_number
            // track_number = 1: last lane is C (65 + 2 = 67)
            // track_number = 2: last lane is F (65 + 5 = 70)
            // track_number = 3: last lane is I (65 + 8 = 73)
            // track_number = 4: last lane is L (65 + 11 = 76)
            $lastLaneIndex = ($tournament->track_number * 3) - 1;
            $lastLaneChar = chr(65 + $lastLaneIndex);

            \Log::info('Race balancing - Tournament track_number: ' . $tournament->track_number);

            // Move teams to balance tracks
            $moves = 0;
            $moveDetails = [];

            foreach ($tracksNeedingBalance as $track) {
                // Calculate lane letters for this track
                // Track 1: A, B, C
                // Track 2: D, E, F
                // Track 3: G, H, I
                // Track 4: J, K, L
                $firstLane = chr(65 + ($track - 1) * 3);     // A, D, G, J...
                $secondLane = chr(65 + ($track - 1) * 3 + 1); // B, E, H, K...
                $thirdLane = chr(65 + ($track - 1) * 3 + 2);  // C, F, I, L...

                $raceToMove = null;
                $sourceDescription = '';

                if (!$usePreviousRace && !empty($tracksWithExtraLanes)) {
                    // Get a team from a track with extra lanes (3 lanes)
                    $sourceTrack = $tracksWithExtraLanes[0];
                    $sourceThirdLane = chr(65 + ($sourceTrack - 1) * 3 + 2); // C, F, I, L...
                    
                    // Find the race in the third lane of the source track
                    foreach ($lastRaceByTrack[$sourceTrack] as $race) {
                        if ($race->lane === $sourceThirdLane) {
                            $raceToMove = $race;
                            $sourceDescription = 'Race ' . $lastRaceNo . ' Track ' . $sourceTrack . ' Lane ' . $sourceThirdLane;
                            break;
                        }
                    }

                    // Remove this track from tracksWithExtraLanes since we're using one lane
                    if (count($lastRaceByTrack[$sourceTrack]) <= 2) {
                        array_shift($tracksWithExtraLanes);
                    }
                } else {
                    // Get the last team from the previous race (highest lane)
                    if (empty($previousRaceRaces)) {
                        break;
                    }

                    // Sort previous race by lane to get the highest lane
                    usort($previousRaceRaces, function($a, $b) {
                        return strcmp($b->lane, $a->lane);
                    });

                    $raceToMove = array_shift($previousRaceRaces);
                    $sourceDescription = 'Race ' . $previousRaceNo . ' Lane ' . $raceToMove->lane;
                }

                if (!$raceToMove) {
                    break;
                }

                // Find the existing team in the last race that's in the first lane of this track
                $existingTeamRace = null;
                foreach ($lastRaceRaces as $race) {
                    if ((int) $race->track === $track && $race->lane === $firstLane) {
                        $existingTeamRace = $race;
                        break;
                    }
                }

                // Log move details
                if ($existingTeamRace) {
                    $moveDetails[] = [
                        'move_type' => 'swap',
                        'track' => $track,
                        'from' => $sourceDescription . ' (' . ($raceToMove->team->team_name ?? 'Unknown') . ')',
                        'to' => 'Race ' . $lastRaceNo . ' Track ' . $track . ' Lane ' . $firstLane,
                        'existing_team_move' => 'Race ' . $lastRaceNo . ' Track ' . $track . ' Lane ' . $firstLane . ' (' . ($existingTeamRace->team->team_name ?? 'Unknown') . ') to Lane ' . $secondLane
                    ];
                } else {
                    $moveDetails[] = [
                        'move_type' => 'move',
                        'track' => $track,
                        'from' => $sourceDescription,
                        'to' => 'Race ' . $lastRaceNo . ' Track ' . $track . ' Lane ' . $firstLane,
                        'team' => $raceToMove->team->team_name ?? 'Unknown'
                    ];
                }

                // IMPORTANT: Move the existing team in the last race FIRST to the second lane (B, E, H, K...)
                // This frees up the first lane (A, D, G, J...) and avoids duplicate key constraint
                if ($existingTeamRace) {
                    $existingTeamRace->lane = $secondLane;
                    $existingTeamRace->save();
                }

                // Then move the team to the last race's first lane of this track
                $raceToMove->race_no = $lastRaceNo;
                $raceToMove->lane = $firstLane;
                $raceToMove->track = (string) $track;
                $raceToMove->save();

                $moves++;
            }

            DB::commit();

            \Log::info('Race balancing - Completed. Moves: ' . $moves, $moveDetails);

            return response()->json([
                'success' => "Races balanced successfully. {$moves} team(s) moved.",
                'moves' => $moves,
                'details' => $moveDetails
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Race balancing failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to balance races: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Convert all races in a stage to use only 1 track.
     * 
     * This will redistribute all racers from multiple tracks into new races
     * using only Track 1 with lanes A, B, C.
     */
    public function convertToSingleTrack(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return response()->json(['error' => 'Please select a tournament first.'], 400);
        }

        $validated = $request->validate([
            'stage' => 'required|integer|min:1',
        ]);

        $stage = $validated['stage'];

        DB::beginTransaction();
        try {
            // Check if already using 1 track
            if ($tournament->track_number == 1) {
                return response()->json(['info' => 'Tournament is already using 1 track.'], 200);
            }

            // Get all races in the stage, ordered by race_no and lane
            $races = Race::where('tournament_id', $tournament->id)
                ->where('stage', $stage)
                ->orderBy('race_no')
                ->orderBy('lane')
                ->get();

            if ($races->isEmpty()) {
                return response()->json(['error' => 'No races found in stage ' . $stage], 404);
            }

            // Group races by race_no
            $racesByRaceNo = [];
            foreach ($races as $race) {
                $raceNo = $race->race_no ?? 0;
                if (!isset($racesByRaceNo[$raceNo])) {
                    $racesByRaceNo[$raceNo] = [];
                }
                $racesByRaceNo[$raceNo][] = $race;
            }

            // Sort race numbers
            ksort($racesByRaceNo);

            // Collect all race data to recreate
            $raceDataToRecreate = [];
            $totalRacesConverted = 0;
    
            // Collect all racers from each original race and redistribute
            $newRaceNo = 1;
            $lanes = ['A', 'B', 'C'];
            $laneIndex = 0;

            foreach ($racesByRaceNo as $originalRaceNo => $raceList) {
                foreach ($raceList as $race) {
                    // Store race data for recreation
                    $raceDataToRecreate[] = [
                        'id' => $race->id,
                        'tournament_id' => $race->tournament_id,
                        'stage' => $race->stage,
                        'race_no' => $newRaceNo,
                        'track' => '1',
                        'lane' => $lanes[$laneIndex],
                        'racer_id' => $race->racer_id,
                        'team_id' => $race->team_id,
                        'card_id' => $race->card_id,
                        'race_time' => $race->race_time,
                        'is_called' => $race->is_called,
                        'created_by' => $race->created_by,
                        'updated_by' => auth()->id(),
                    ];

                    $laneIndex++;
                    if ($laneIndex >= 3) {
                        $laneIndex = 0;
                        $newRaceNo++;
                    }
                }

                $totalRacesConverted += count($raceList);

                // If we have a partial race (less than 3 racers), move to next race_no
                if ($laneIndex > 0) {
                    $newRaceNo++;
                }
            }

            // Delete all existing races in the stage to avoid unique constraint violations
            Race::where('tournament_id', $tournament->id)
                ->where('stage', $stage)
                ->delete();

            // Recreate races with new track and lane assignments
            foreach ($raceDataToRecreate as $data) {
                Race::create($data);
            }

            // Update tournament's track_number to 1
            // $tournament->track_number = 1;
            // $tournament->save();

            DB::commit();

            \Log::info('Race conversion to single track completed', [
                'tournament_id' => $tournament->id,
                'stage' => $stage,
                'total_races' => $totalRacesConverted,
                'new_race_count' => $newRaceNo - 1
            ]);

            return response()->json([
                'success' => "Successfully converted {$totalRacesConverted} races to 1 track in Stage {$stage}.",
                'total_races' => $totalRacesConverted,
                'new_race_count' => $newRaceNo - 1,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Race conversion to single track failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to convert races: ' . $e->getMessage()], 500);
        }
    }
}
