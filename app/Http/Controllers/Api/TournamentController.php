<?php

namespace App\Http\Controllers\Api;

use App\Models\Tournament;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class TournamentController extends Controller
{
    public function index()
    {
        $tournaments = Tournament::all();

        return response()->json([
            'success' => true,
            'message' => 'Tournament list retrieved successfully',
            'data' => $tournaments
        ]);
    }

    public function show($id)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tournament retrieved successfully',
            'data' => $tournament
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tournament_name' => 'required|string|max:255',
            'vendor_name' => 'nullable|string|max:255',
            'status' => 'in:PLANNED,ACTIVE,COMPLETED,CANCELLED',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $tournament = Tournament::create([
            'id' => Str::uuid(),
            'tournament_name' => $request->tournament_name,
            'vendor_name' => $request->vendor_name,
            'current_stage' => $request->current_stage,
            'current_bto_session' => $request->current_bto_session,
            'track_number' => $request->track_number ?? 1,
            'bto_number' => $request->bto_number ?? 1,
            'bto_session_number' => $request->bto_session_number ?? 0,
            'max_racer_per_team' => $request->max_racer_per_team ?? 1,
            'champion_number' => $request->champion_number ?? 3,
            'best_race_enabled' => $request->best_race_enabled ?? false,
            'best_race_number' => $request->best_race_number,
            'status' => $request->status ?? 'PLANNED',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tournament created successfully',
            'data' => $tournament
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tournament_name' => 'sometimes|required|string|max:255',
            'vendor_name' => 'nullable|string|max:255',
            'status' => 'in:PLANNED,ACTIVE,COMPLETED,CANCELLED',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $tournament->update(array_merge(
            $request->only([
                'tournament_name', 'vendor_name', 'current_stage', 'current_bto_session',
                'track_number', 'bto_number', 'bto_session_number', 'max_racer_per_team',
                'champion_number', 'best_race_enabled', 'best_race_number', 'status',
            ]),
            ['updated_by' => auth()->id()]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Tournament updated successfully',
            'data' => $tournament
        ]);
    }

    public function destroy($id)
    {
        $tournament = Tournament::find($id);

        if (!$tournament) {
            return response()->json([
                'success' => false,
                'message' => 'Tournament not found',
            ], 404);
        }

        $tournament->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tournament deleted successfully'
        ]);
    }
}
