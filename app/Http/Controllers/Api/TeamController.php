<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Team::all()
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'team_name' => 'required|string|unique:teams,team_name'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 400);
        }

        $team = Team::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'team_name' => $validated['team_name'],
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team created successfully',
            'data' => $team
        ], 201);
    }


    public function show($id)
    {
        $team = Team::find($id);

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $team,
        ]);
    }


    public function update(Request $request, $id)
    {
        $team = Team::find($id);

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found',
            ], 404);
        }

        try {
            $request->validate([
                'team_name' => 'sometimes|string|unique:teams,team_name,' . $team->id
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 400);
        }

        $team->update([
            'team_name' => $request->team_name ?? $team->team_name,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team updated successfully',
            'data' => $team
        ]);
    }

    public function destroy($id)
    {
        $team = Team::find($id);

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => 'Team not found',
            ], 404);
        }

        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team deleted successfully',
        ]);
    }
}
