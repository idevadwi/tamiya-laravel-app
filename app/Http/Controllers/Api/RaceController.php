<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Race;
use App\Models\Card;
use App\Models\Racer;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\TournamentParticipant;
use App\Helpers\AblyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RaceController extends Controller
{
    /**
     * Store a newly created race.
     *
     * Query parameters:
     * - tournament_id (optional): UUID of the tournament
     * - tournament_slug (optional): Slug of the tournament
     * - card_code (required): Card code string
     *
     * Note: Either tournament_id OR tournament_slug must be provided
     */
    public function store(Request $request)
    {
        // Validate that either tournament_id or tournament_slug is provided
        $validator = Validator::make($request->all(), [
            'tournament_id' => 'sometimes|required_without:tournament_slug|uuid|exists:tournaments,id',
            'tournament_slug' => 'sometimes|required_without:tournament_id|string|exists:tournaments,slug',
            'card_code' => 'required|string|exists:cards,card_code',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get tournament by id or slug
        if ($request->has('tournament_id')) {
            $tournament = Tournament::findOrFail($request->tournament_id);
        } else {
            $tournament = Tournament::where('slug', $request->tournament_slug)->firstOrFail();
        }

        // Get card by card_code and verify it belongs to tournament
        $card = Card::with('racer.team')->where('card_code', $request->card_code)->first();
        
        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found.',
            ], 404);
        }

        // Verify card's racer belongs to tournament
        if (!$card->racer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Card is not assigned to any racer.',
            ], 400);
        }

        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');

        $isValidRacer = Racer::whereIn('team_id', $teamIds)
            ->where('id', $card->racer_id)
            ->exists();

        if (!$isValidRacer) {
            return response()->json([
                'success' => false,
                'message' => 'Card does not belong to the active tournament.',
            ], 400);
        }

        // Get racer and team
        $racer = $card->racer;
        $team = $racer->team;

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Racer is not assigned to any team.',
            ], 400);
        }

        // Calculate stage (current_stage + 1)
        $stage = $tournament->current_stage + 1;

        // Calculate track and lane
        $trackAndLane = $this->calculateTrackAndLane($tournament, $stage);

        // Check if stage + race_no + lane combination already exists
        $existingRace = Race::where('tournament_id', $tournament->id)
            ->where('stage', $stage)
            ->where('race_no', $trackAndLane['race_no'])
            ->where('lane', $trackAndLane['lane'])
            ->first();

        if ($existingRace) {
            return response()->json([
                'success' => false,
                'message' => 'A race already exists for Stage ' . $stage . ', Race No ' . $trackAndLane['race_no'] . ', Lane ' . $trackAndLane['lane'] . '.',
            ], 409); // 409 Conflict
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

        // Load relationships for response
        $race->load(['tournament', 'racer.team', 'team', 'card']);

        if ($tournament->best_race_live_update) {
            $this->publishBestRaceUpdate($tournament);
        }

        return response()->json([
            'success' => true,
            'message' => 'Race created successfully',
            'race_no' => $trackAndLane['race_no'],
            'track' => $trackAndLane['track'],
            'lane' => $trackAndLane['lane'],
        ], 201);
    }

    /**
     * Publish best race update to Ably
     */
    private function publishBestRaceUpdate($tournament)
    {
        $nextStage = $tournament->current_stage + 1;

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

        AblyHelper::publishBestRace($tournament, $topTeams);
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
}
