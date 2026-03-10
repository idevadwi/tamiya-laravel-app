<?php

namespace App\Http\Controllers\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\TournamentCardAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CardReturnController extends Controller
{
    /**
     * Show the card return page with history.
     */
    public function index()
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        $assignments = TournamentCardAssignment::where('tournament_id', $tournament->id)
            ->join('cards', 'tournament_card_assignments.card_id', '=', 'cards.id')
            ->join('racers', 'tournament_card_assignments.racer_id', '=', 'racers.id')
            ->join('teams', 'racers.team_id', '=', 'teams.id')
            ->select('tournament_card_assignments.*')
            ->with(['card', 'racer.team'])
            ->orderByRaw('ISNULL(returned_at) DESC, returned_at DESC, cards.card_no ASC')
            ->get();

        $totalCards    = $assignments->count();
        $returnedCards = $assignments->whereNotNull('returned_at')->count();
        $pendingCards  = $totalCards - $returnedCards;

        return view('tournament.card-returns.index', compact(
            'tournament', 'assignments', 'totalCards', 'returnedCards', 'pendingCards'
        ));
    }

    /**
     * Mark a card as returned (AJAX).
     */
    public function store(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'No active tournament found.'], 422);
        }

        $request->validate([
            'input_type' => 'required|in:card_code,card_no',
            'card_code'  => 'required_if:input_type,card_code|nullable|string',
            'card_no'    => 'required_if:input_type,card_no|nullable|string',
        ]);

        $inputType  = $request->input_type;
        $inputValue = $request->$inputType;

        $card = Card::where($inputType, $inputValue)->first();

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Card not found.',
            ], 422);
        }

        $assignment = TournamentCardAssignment::where('tournament_id', $tournament->id)
            ->where('card_id', $card->id)
            ->with(['racer.team'])
            ->first();

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'This card is not assigned in the current tournament.',
            ], 422);
        }

        if ($assignment->returned_at) {
            return response()->json([
                'success' => false,
                'message' => 'This card has already been returned on ' . $assignment->returned_at->format('d M Y H:i') . '.',
            ], 422);
        }

        $assignment->update([
            'returned_at' => Carbon::now(),
            'updated_by'  => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card returned successfully.',
            'data'    => [
                'card_no'     => $card->card_no,
                'card_code'   => $card->card_code,
                'racer_name'  => $assignment->racer->racer_name,
                'team_name'   => $assignment->racer->team->team_name,
                'returned_at' => $assignment->returned_at->format('d M Y H:i:s'),
            ],
        ]);
    }

    /**
     * Undo a card return (AJAX).
     */
    public function undo(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'No active tournament found.'], 422);
        }

        $request->validate([
            'card_id' => 'required|exists:cards,id',
        ]);

        $assignment = TournamentCardAssignment::where('tournament_id', $tournament->id)
            ->where('card_id', $request->card_id)
            ->first();

        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'Assignment not found.'], 422);
        }

        $assignment->update([
            'returned_at' => null,
            'updated_by'  => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Return undone successfully.']);
    }
}
