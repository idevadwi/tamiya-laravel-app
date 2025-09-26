<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CardController extends Controller
{
    // List all cards
    public function index()
    {
        $cards = Card::with('racer')->get();

        return response()->json([
            'success' => true,
            'message' => 'Card list retrieved successfully',
            'data' => $cards
        ], 200);
    }

    // Show single card
    public function show($id)
    {
        $card = Card::with('racer')->find($id);

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Card retrieved successfully',
            'data' => $card
        ], 200);
    }

    // Store new card
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_code' => 'required|string|unique:cards,card_code',
            'racer_id'  => 'nullable|exists:racers,id',
            'coupon'    => 'nullable|integer|min:0',
            'status'    => 'nullable|in:ACTIVE,LOST,BANNED',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $card = Card::create([
            'id'        => Str::uuid(),
            'card_code' => $request->card_code,
            'racer_id'  => $request->racer_id,
            'coupon'    => $request->coupon ?? 0,
            'status'    => $request->status ?? 'ACTIVE',
            'created_by'=> auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card created successfully',
            'data' => $card
        ], 201);
    }

    // Update card
    public function update(Request $request, $id)
    {
        $card = Card::find($id);

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'card_code' => 'sometimes|string|unique:cards,card_code,' . $card->id,
            'racer_id'  => 'nullable|exists:racers,id',
            'coupon'    => 'nullable|integer|min:0',
            'status'    => 'nullable|in:ACTIVE,LOST,BANNED',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $card->update([
            'card_code' => $request->card_code ?? $card->card_code,
            'racer_id'  => $request->racer_id,
            'coupon'    => $request->coupon ?? $card->coupon,
            'status'    => $request->status ?? $card->status,
            'updated_by'=> auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card updated successfully',
            'data' => $card
        ], 200);
    }

    // Delete card
    public function destroy($id)
    {
        $card = Card::find($id);

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found',
            ], 404);
        }

        $card->delete();

        return response()->json([
            'success' => true,
            'message' => 'Card deleted successfully'
        ], 200);
    }

    // Reassign card to another racer or unassign
    public function reassign(Request $request, $id)
    {
        $card = Card::find($id);

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'racer_id' => 'nullable|exists:racers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $card->update([
            'racer_id' => $request->racer_id, // can be null to unassign
            'updated_by'=> auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card reassigned successfully',
            'data' => $card
        ], 200);
    }

    // Get card by card_code
    public function getByCode($card_code)
    {
        $card = Card::with('racer')->where('card_code', $card_code)->first();

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Card retrieved successfully',
            'data' => $card
        ], 200);
    }

    // Get all cards for a racer_id
    public function getByRacer($racer_id)
    {
        $cards = Card::with('racer')->where('racer_id', $racer_id)->get();

        if ($cards->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No cards found for this racer',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cards retrieved successfully',
            'data' => $cards
        ], 200);
    }
}
