<?php

namespace App\Http\Controllers\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Racer;
use App\Models\Team;
use App\Models\TournamentCardAssignment;
use App\Models\TournamentRacerParticipant;
use Illuminate\Http\Request;

class CardController extends Controller
{
    /**
     * Display a listing of cards assigned in the active tournament.
     */
    public function index(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Validate sort parameters
        $allowedSorts = ['card_no', 'racer_name', 'team_name', 'status'];
        $sort      = in_array($request->sort, $allowedSorts) ? $request->sort : 'card_no';
        $direction = $request->direction === 'desc' ? 'desc' : 'asc';

        $sortColumn = match ($sort) {
            'racer_name' => 'racers.racer_name',
            'team_name'  => 'teams.team_name',
            'status'     => 'tournament_card_assignments.status',
            default      => 'cards.card_no',
        };

        $query = TournamentCardAssignment::where('tournament_card_assignments.tournament_id', $tournament->id)
            ->join('cards',  'tournament_card_assignments.card_id',  '=', 'cards.id')
            ->join('racers', 'tournament_card_assignments.racer_id', '=', 'racers.id')
            ->join('teams',  'racers.team_id', '=', 'teams.id')
            ->select('tournament_card_assignments.*')
            ->with(['card', 'racer.team']);

        // Filter by team
        if ($request->filled('team_id')) {
            $query->where('teams.id', $request->team_id);
        }

        // Filter by racer
        if ($request->filled('racer_id')) {
            $query->where('tournament_card_assignments.racer_id', $request->racer_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('tournament_card_assignments.status', $request->status);
        }

        // Search by card_no or card_code
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('cards.card_no', 'like', '%' . $request->search . '%')
                  ->orWhere('cards.card_code', 'like', '%' . $request->search . '%');
            });
        }

        $assignments = $query->orderBy($sortColumn, $direction)->paginate(10);
        $assignments->appends($request->query());

        // Teams for filter dropdown
        $teams = Team::whereHas('tournamentParticipants', function ($q) use ($tournament) {
            $q->where('tournament_id', $tournament->id);
        })->orderBy('team_name')->get();

        // Racers for filter dropdown
        $racerIds = TournamentRacerParticipant::where('tournament_id', $tournament->id)->pluck('racer_id');
        $racers   = Racer::whereIn('id', $racerIds)->with('team')->orderBy('racer_name')->get();

        return view('tournament.cards.index', compact(
            'assignments', 'tournament', 'racers', 'teams', 'sort', 'direction'
        ));
    }

    /**
     * Show the form for assigning a card to a tournament racer.
     */
    public function create(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Get racers participating in this tournament
        $racerIds = TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->pluck('racer_id');

        $racers = Racer::whereIn('id', $racerIds)
            ->with('team')
            ->orderBy('racer_name')
            ->get();

        // Pre-select racer if provided via query parameter
        $selectedRacerId = $request->get('racer_id');

        // Cards not yet assigned in this tournament, must be ACTIVE and have a card_no
        $assignedCardIds = TournamentCardAssignment::where('tournament_id', $tournament->id)
            ->pluck('card_id');

        $availableCards = Card::whereNotIn('id', $assignedCardIds)
            ->where('status', 'ACTIVE')
            ->whereNotNull('card_no')
            ->orderBy('card_no')
            ->get();

        return view('tournament.cards.create', compact(
            'tournament', 'racers', 'selectedRacerId', 'availableCards'
        ));
    }

    /**
     * Assign a card to a racer in the active tournament.
     */
    public function store(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Validate racer belongs to tournament
        if ($request->racer_id) {
            $isValidRacer = TournamentRacerParticipant::where('tournament_id', $tournament->id)
                ->where('racer_id', $request->racer_id)
                ->exists();

            if (!$isValidRacer) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Selected racer does not belong to the active tournament.');
            }
        }

        $validated = $request->validate([
            'card_id'  => 'required|exists:cards,id',
            'racer_id' => 'required|uuid|exists:racers,id',
        ]);

        // Ensure the card is ACTIVE and not already assigned in this tournament
        $card = Card::findOrFail($validated['card_id']);

        if ($card->status !== 'ACTIVE') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'The selected card is not active and cannot be assigned.');
        }

        $alreadyAssigned = TournamentCardAssignment::where('tournament_id', $tournament->id)
            ->where('card_id', $validated['card_id'])
            ->exists();

        if ($alreadyAssigned) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'The selected card is already assigned in this tournament.');
        }

        TournamentCardAssignment::create([
            'tournament_id' => $tournament->id,
            'card_id'       => $validated['card_id'],
            'racer_id'      => $validated['racer_id'],
            'status'        => 'ACTIVE',
            'created_by'    => auth()->id(),
        ]);

        return redirect()->route('tournament.cards.index')
            ->with('success', 'Card assigned successfully.');
    }

    /**
     * Display the specified card assignment in this tournament.
     */
    public function show(Card $card)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        $assignment = TournamentCardAssignment::where('tournament_id', $tournament->id)
            ->where('card_id', $card->id)
            ->with(['racer.team'])
            ->firstOrFail();

        return view('tournament.cards.show', compact('card', 'assignment', 'tournament'));
    }

    /**
     * Show the form for editing the card assignment.
     */
    public function edit(Card $card)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        $assignment = TournamentCardAssignment::where('tournament_id', $tournament->id)
            ->where('card_id', $card->id)
            ->firstOrFail();

        $racerIds = TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->pluck('racer_id');

        $racers = Racer::whereIn('id', $racerIds)
            ->with('team')
            ->orderBy('racer_name')
            ->get();

        return view('tournament.cards.edit', compact('card', 'assignment', 'tournament', 'racers'));
    }

    /**
     * Update the card assignment (racer and/or status).
     */
    public function update(Request $request, Card $card)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        $assignment = TournamentCardAssignment::where('tournament_id', $tournament->id)
            ->where('card_id', $card->id)
            ->firstOrFail();

        // Validate racer belongs to tournament
        if ($request->racer_id) {
            $isValidRacer = TournamentRacerParticipant::where('tournament_id', $tournament->id)
                ->where('racer_id', $request->racer_id)
                ->exists();

            if (!$isValidRacer) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Selected racer does not belong to the active tournament.');
            }
        }

        $validated = $request->validate([
            'racer_id' => 'required|uuid|exists:racers,id',
            'status'   => 'required|in:ACTIVE,LOST,BANNED',
        ]);

        $assignment->update([
            'racer_id'   => $validated['racer_id'],
            'status'     => $validated['status'],
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('tournament.cards.index')
            ->with('success', 'Card assignment updated successfully.');
    }

    /**
     * Bulk remove card assignments from the active tournament.
     */
    public function bulkDestroy(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        $validated = $request->validate([
            'card_ids'   => 'required|array|min:1',
            'card_ids.*' => 'uuid|exists:cards,id',
        ]);

        $deleted = TournamentCardAssignment::where('tournament_id', $tournament->id)
            ->whereIn('card_id', $validated['card_ids'])
            ->delete();

        return redirect()->route('tournament.cards.index')
            ->with('success', "{$deleted} card assignment(s) removed successfully.");
    }

    /**
     * Remove the card assignment from the active tournament (does not delete the master card).
     */
    public function destroy(Card $card)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        $assignment = TournamentCardAssignment::where('tournament_id', $tournament->id)
            ->where('card_id', $card->id)
            ->firstOrFail();

        $assignment->delete();

        return redirect()->route('tournament.cards.index')
            ->with('success', 'Card assignment removed successfully.');
    }
}
