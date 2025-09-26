<?php

namespace App\Http\Controllers\Api;

use App\Models\Racer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class RacerController extends Controller
{
    /**
     * Display a listing of racers.
     */
    public function index()
    {
        $racers = Racer::with('team')->get();

        return response()->json([
            'success' => true,
            'message' => 'Racers fetched successfully',
            'data' => $racers
        ], 200);
    }

    /**
     * Store a newly created racer.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'racer_name' => 'required|string|max:255',
            'image'      => 'nullable|string',
            'team_id'    => 'nullable|uuid|exists:teams,id',
        ], [
            'racer_name.required' => 'racer_name is required.',
            'team_id.uuid'        => 'team_id must be a valid UUID.',
            'team_id.exists'      => 'team_id not found.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 400);
        }

        $racer = Racer::create([
            'id'         => \Illuminate\Support\Str::uuid(),
            'racer_name' => $request->racer_name,
            'image'      => $request->image,
            'team_id'    => $request->team_id,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Racer created successfully',
            'data'    => $racer,
        ], 201);
    }

    /**
     * Display the specified racer.
     */
    public function show($id)
    {
        $racer = Racer::with('team')->find($id);

        if (!$racer) {
            return response()->json([
                'success' => false,
                'message' => 'Racer not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Racer fetched successfully',
            'data' => $racer
        ], 200);
    }

    /**
     * Update the specified racer.
     */
    public function update(Request $request, $id)
    {
        $racer = Racer::find($id);
        if (!$racer) {
            return response()->json([
                'success' => false,
                'message' => 'Racer not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'racer_name' => 'sometimes|required|string|max:255',
            'image'      => 'nullable|string',
            'team_id'    => 'nullable|uuid|exists:teams,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 400);
        }

        $racer->update([
            'racer_name' => $request->racer_name ?? $racer->racer_name,
            'image'      => $request->image ?? $racer->image,
            'team_id'    => $request->team_id ?? $racer->team_id,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Racer updated successfully',
            'data'    => $racer,
        ], 200);
    }


    /**
     * Remove the specified racer.
     */
    public function destroy($id)
    {
        $racer = Racer::find($id);

        if (!$racer) {
            return response()->json([
                'success' => false,
                'message' => 'Racer not found'
            ], 404);
        }

        $racer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Racer deleted successfully'
        ], 200);
    }

    /**
     * Assign racer to a team.
     */
    public function assignTeam(Request $request, $id)
    {
        $racer = Racer::find($id);
        if (!$racer) {
            return response()->json([
                'success' => false,
                'message' => 'Racer not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'team_id' => 'nullable|uuid|exists:teams,id',
        ], [
            'team_id.uuid'   => 'team_id must be a valid UUID.',
            'team_id.exists' => 'team_id not found.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $racer->update([
            'team_id'    => $request->team_id, // can be null
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $request->team_id
                ? 'Racer assigned to team successfully'
                : 'Racer unassigned from team successfully',
            'data'    => $racer
        ], 200);
    }
}
