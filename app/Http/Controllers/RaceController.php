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
            'viewMode'
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
}
