<?php

namespace App\Http\Controllers\Api;

use App\Models\Racer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class RacerController extends Controller
{
    private const TEAM_VALIDATION_RULE = 'nullable|uuid|exists:teams,id';
    private const VALIDATION_ERROR_MESSAGE = 'Validation error';
    private const RACER_NOT_FOUND_MESSAGE = 'Racer not found';
    /**
     * Display a listing of racers.
     */
    public function index()
    {
        $racers = Racer::with('team')->get();

        // Image URLs are automatically added via the model accessor

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
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'team_id'    => self::TEAM_VALIDATION_RULE,
        ], [
            'racer_name.required' => 'racer_name is required.',
            'image.image'         => 'The file must be an image.',
            'image.mimes'         => 'The image must be a file of type: jpeg, png, jpg, gif, svg.',
            'image.max'           => 'The image may not be greater than 2MB.',
            'team_id.uuid'        => 'team_id must be a valid UUID.',
            'team_id.exists'      => 'team_id not found.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => self::VALIDATION_ERROR_MESSAGE,
                'errors'  => $validator->errors(),
            ], 400);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('racers', $imageName, 'public');
        }

        $racer = Racer::create([
            'id'         => \Illuminate\Support\Str::uuid(),
            'racer_name' => $request->racer_name,
            'image'      => $imagePath,
            'team_id'    => $request->team_id,
            'created_by' => auth()->id(),
        ]);

        // Load the racer with team relationship
        $racer->load('team');

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
                'message' => self::RACER_NOT_FOUND_MESSAGE
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
                'message' => self::RACER_NOT_FOUND_MESSAGE,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'racer_name' => 'sometimes|required|string|max:255',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'team_id'    => self::TEAM_VALIDATION_RULE,
        ], [
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, svg.',
            'image.max'   => 'The image may not be greater than 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => self::VALIDATION_ERROR_MESSAGE,
                'errors'  => $validator->errors(),
            ], 400);
        }

        $updateData = [
            'racer_name' => $request->racer_name ?? $racer->racer_name,
            'team_id'    => $request->team_id ?? $racer->team_id,
            'updated_by' => auth()->id(),
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($racer->image && \Storage::disk('public')->exists($racer->image)) {
                \Storage::disk('public')->delete($racer->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('racers', $imageName, 'public');
            $updateData['image'] = $imagePath;
        }

        $racer->update($updateData);

        // Load the racer with team relationship
        $racer->load('team');

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
                'message' => self::RACER_NOT_FOUND_MESSAGE
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
                'message' => self::RACER_NOT_FOUND_MESSAGE
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'team_id' => self::TEAM_VALIDATION_RULE,
        ], [
            'team_id.uuid'   => 'team_id must be a valid UUID.',
            'team_id.exists' => 'team_id not found.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => self::VALIDATION_ERROR_MESSAGE,
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
