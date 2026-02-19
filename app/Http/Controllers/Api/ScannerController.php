<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScannerDevice;
use App\Models\Race;
use App\Models\Card;
use App\Models\Racer;
use App\Models\TournamentParticipant;
use App\Helpers\AblyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ScannerController extends Controller
{
    /**
     * Store a race from a scanner device.
     *
     * Headers:
     * - X-Device-Code (required): The device's unique identifier (MAC address)
     *
     * Body:
     * - card_code (required): The scanned card code
     */
    public function storeRace(Request $request)
    {
        try {
            $deviceCode = $request->header('X-Device-Code');

            if (!$deviceCode) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'DEVICE_CODE_MISSING',
                    'message' => 'X-Device-Code header is required.',
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'card_code' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'VALIDATION_ERROR',
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $device = ScannerDevice::with('tournament')->where('device_code', $deviceCode)->first();

            if (!$device) {
                \Log::error('Scanner device not found', ['device_code' => $deviceCode]);
                return response()->json([
                    'success' => false,
                    'error_code' => 'DEVICE_NOT_REGISTERED',
                    'message' => 'Device is not registered. Please contact administrator.',
                ], 404);
            }

            $device->updateLastSeen();

            if ($device->status !== 'ACTIVE') {
                \Log::error('Scanner device not active', ['device_code' => $deviceCode, 'status' => $device->status]);
                return response()->json([
                    'success' => false,
                    'error_code' => 'DEVICE_INACTIVE',
                    'message' => "Device is {$device->status}. Please contact administrator.",
                ], 403);
            }

            if (!$device->tournament_id) {
                \Log::error('Scanner device not linked to tournament', ['device_code' => $deviceCode]);
                return response()->json([
                    'success' => false,
                    'error_code' => 'DEVICE_NOT_LINKED',
                    'message' => 'Device is not linked to any tournament.',
                ], 400);
            }

            $tournament = $device->tournament;

            if (!$tournament) {
                \Log::error('Tournament not found for scanner device', [
                    'device_code' => $deviceCode,
                    'device_id' => $device->id,
                    'tournament_id' => $device->tournament_id
                ]);
                return response()->json([
                    'success' => false,
                    'error_code' => 'TOURNAMENT_NOT_FOUND',
                    'message' => 'Linked tournament not found. The tournament may have been deleted. Please contact administrator.',
                ], 404);
            }

            if ($tournament->status !== 'ACTIVE') {
                return response()->json([
                    'success' => false,
                    'error_code' => 'TOURNAMENT_NOT_ACTIVE',
                    'message' => "Tournament is {$tournament->status}.",
                ], 400);
            }

            $card = Card::with('racer.team')->where('card_code', $request->card_code)->first();

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'CARD_NOT_FOUND',
                    'message' => 'Card not found.',
                ], 404);
            }

            if (!$card->racer_id) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'CARD_NOT_ASSIGNED',
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
                    'error_code' => 'RACER_NOT_IN_TOURNAMENT',
                    'message' => 'Racer is not participating in this tournament.',
                ], 400);
            }

            $racer = $card->racer;
            $team = $racer->team;

            if (!$team) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'RACER_NO_TEAM',
                    'message' => 'Racer is not assigned to any team.',
                ], 400);
            }

            $stage = $tournament->current_stage + 1;
            $trackAndLane = $this->calculateTrackAndLane($tournament, $stage);

            $existingRace = Race::where('tournament_id', $tournament->id)
                ->where('stage', $stage)
                ->where('race_no', $trackAndLane['race_no'])
                ->where('lane', $trackAndLane['lane'])
                ->first();

            if ($existingRace) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'RACE_SLOT_OCCUPIED',
                    'message' => "Race slot already occupied: Stage {$stage}, Race No {$trackAndLane['race_no']}, Lane {$trackAndLane['lane']}.",
                ], 409);
            }

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
                'race_time' => null,
                'created_by' => null,
            ]);

            if ($tournament->best_race_live_update && $stage === 2) {
                $this->publishBestRaceUpdate($tournament);
            }

            return response()->json([
                'success' => true,
                'message' => 'Race created successfully',
                'data' => [
                    'race_no' => $trackAndLane['race_no'],
                    'track' => $trackAndLane['track'],
                    'lane' => $trackAndLane['lane'],
                    'racer_name' => $racer->racer_name,
                    'team_name' => $team->team_name,
                    'tournament_name' => $tournament->tournament_name,
                    'stage' => $stage,
                ]
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Model not found exception in ScannerController@storeRace', [
                'message' => $e->getMessage(),
                'model' => $e->getModel(),
                'ids' => $e->getIds(),
            ]);
            return response()->json([
                'success' => false,
                'error_code' => 'MODEL_NOT_FOUND',
                'message' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Unexpected exception in ScannerController@storeRace', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error_code' => 'INTERNAL_ERROR',
                'message' => 'An unexpected error occurred. Please contact administrator.',
            ], 500);
        }
    }

    /**
     * Device heartbeat/status endpoint.
     */
    public function heartbeat(Request $request)
    {
        try {
            $deviceCode = $request->header('X-Device-Code');

            if (!$deviceCode) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'DEVICE_CODE_MISSING',
                    'message' => 'X-Device-Code header is required.',
                ], 400);
            }

            $device = ScannerDevice::with('tournament')->where('device_code', $deviceCode)->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'DEVICE_NOT_REGISTERED',
                    'message' => 'Device is not registered.',
                ], 404);
            }

            $device->updateLastSeen();

            return response()->json([
                'success' => true,
                'data' => [
                    'device_name' => $device->device_name,
                    'status' => $device->status,
                    'tournament' => $device->tournament ? [
                        'id' => $device->tournament->id,
                        'name' => $device->tournament->tournament_name,
                        'status' => $device->tournament->status,
                    ] : null,
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Model not found exception in ScannerController@heartbeat', [
                'message' => $e->getMessage(),
                'model' => $e->getModel(),
                'ids' => $e->getIds(),
            ]);
            return response()->json([
                'success' => false,
                'error_code' => 'MODEL_NOT_FOUND',
                'message' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Unexpected exception in ScannerController@heartbeat', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error_code' => 'INTERNAL_ERROR',
                'message' => 'An unexpected error occurred. Please contact administrator.',
            ], 500);
        }
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
     */
    private function calculateTrackAndLane($tournament, $stage)
    {
        $racesInStage = Race::where('tournament_id', $tournament->id)
            ->where('stage', $stage)
            ->count();

        $raceIndex = $racesInStage;
        $maxLanes = $tournament->track_number * 3;
        $raceNo = floor($raceIndex / $maxLanes) + 1;
        $laneIndex = $raceIndex % $maxLanes;
        $track = floor($laneIndex / 3) + 1;
        $lane = chr(65 + $laneIndex);

        return [
            'race_no' => $raceNo,
            'track' => (string) $track,
            'lane' => $lane,
        ];
    }
}
