<?php

namespace App\Http\Controllers\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Racer;
use App\Models\Team;
use App\Models\TournamentParticipant;
use App\Models\TournamentRacerParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CardController extends Controller
{
    /**
     * Display a listing of cards in the active tournament.
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
        $sort = in_array($request->sort, $allowedSorts) ? $request->sort : 'card_no';
        $direction = $request->direction === 'desc' ? 'desc' : 'asc';

        // Map sort keys to actual columns
        $sortColumn = match ($sort) {
            'racer_name' => 'racers.racer_name',
            'team_name'  => 'teams.team_name',
            'status'     => 'cards.status',
            default      => 'cards.card_no',
        };

        // Get racers participating in this tournament
        $racerIds = TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->pluck('racer_id');

        // Build query for cards belonging to these racers (joins for sortable relations)
        $query = Card::whereIn('cards.racer_id', $racerIds)
            ->with(['racer.team'])
            ->leftJoin('racers', 'cards.racer_id', '=', 'racers.id')
            ->leftJoin('teams', 'racers.team_id', '=', 'teams.id')
            ->select('cards.*');

        // Filter by team if provided
        if ($request->has('team_id') && $request->team_id) {
            $teamRacerIds = TournamentRacerParticipant::where('tournament_id', $tournament->id)
                ->where('team_id', $request->team_id)
                ->pluck('racer_id');
            $query->whereIn('cards.racer_id', $teamRacerIds);
        }

        // Filter by racer if provided
        if ($request->has('racer_id') && $request->racer_id) {
            $query->where('cards.racer_id', $request->racer_id);
        }

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('cards.status', $request->status);
        }

        // Filter by card code search
        if ($request->has('search') && $request->search) {
            $query->where('cards.card_no', 'like', '%' . $request->search . '%');
        }

        $cards = $query->orderBy($sortColumn, $direction)->paginate(10);
        $cards->appends($request->query());

        // Get teams in this tournament for filter dropdown
        $teams = Team::whereHas('tournamentParticipants', function ($q) use ($tournament) {
            $q->where('tournament_id', $tournament->id);
        })->orderBy('team_name')->get();

        // Get racers in this tournament for filter dropdown
        $racers = Racer::whereIn('id', $racerIds)
            ->with('team')
            ->orderBy('racer_name')
            ->get();

        return view('tournament.cards.index', compact('cards', 'tournament', 'racers', 'teams', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new card (assigning to tournament racer).
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

        // Get unassigned cards that have a card_no set (available for assignment)
        $availableCards = Card::whereNull('racer_id')
            ->whereNotNull('card_no')
            ->orderBy('card_no')
            ->get();

        return view('tournament.cards.create', compact('tournament', 'racers', 'selectedRacerId', 'availableCards'));
    }

    /**
     * Store a newly created card.
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
            'card_id' => 'required|exists:cards,id',
            'racer_id' => 'required|uuid|exists:racers,id',
        ]);

        $card = Card::find($validated['card_id']);

        // Ensure the card is still unassigned
        if ($card->racer_id !== null) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'The selected card is already assigned to another racer.');
        }

        $card->update([
            'racer_id' => $validated['racer_id'],
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('tournament.cards.index')
            ->with('success', 'Card assigned successfully.');
    }

    /**
     * Display the specified card.
     */
    public function show(Card $card)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify card's racer belongs to active tournament
        $isValidRacer = TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->where('racer_id', $card->racer_id)
            ->exists();

        if (!$isValidRacer) {
            return redirect()->route('tournament.cards.index')
                ->with('error', 'Card does not belong to a racer in the active tournament.');
        }

        return view('tournament.cards.show', compact('card', 'tournament'));
    }

    /**
     * Show the form for editing the specified card.
     */
    public function edit(Card $card)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify card's racer belongs to active tournament
        $isValidRacer = TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->where('racer_id', $card->racer_id)
            ->exists();

        if (!$isValidRacer) {
            return redirect()->route('tournament.cards.index')
                ->with('error', 'Card does not belong to a racer in the active tournament.');
        }

        // Get racers participating in this tournament
        $racerIds = TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->pluck('racer_id');

        $racers = Racer::whereIn('id', $racerIds)
            ->with('team')
            ->orderBy('racer_name')
            ->get();

        return view('tournament.cards.edit', compact('card', 'tournament', 'racers'));
    }

    /**
     * Update the specified card.
     */
    public function update(Request $request, Card $card)
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
            'card_code' => 'required|string|max:255|unique:cards,card_code,' . $card->id,
            'racer_id' => 'required|uuid|exists:racers,id',
            'coupon' => 'nullable|integer|min:0',
            'status' => 'nullable|in:ACTIVE,LOST,BANNED',
        ]);

        $card->update([
            'card_code' => $validated['card_code'],
            'racer_id' => $validated['racer_id'],
            'coupon' => $validated['coupon'] ?? $card->coupon,
            'status' => $validated['status'] ?? $card->status,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('tournament.cards.index')
            ->with('success', 'Card updated successfully.');
    }

    /**
     * Bulk delete cards belonging to the active tournament.
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

        $racerIds = TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->pluck('racer_id');

        $deleted = Card::whereIn('id', $validated['card_ids'])
            ->whereIn('racer_id', $racerIds)
            ->delete();

        return redirect()->route('tournament.cards.index')
            ->with('success', "{$deleted} card(s) deleted successfully.");
    }

    /**
     * Remove the specified card.
     */
    public function destroy(Card $card)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify card's racer belongs to active tournament
        $isValidRacer = TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->where('racer_id', $card->racer_id)
            ->exists();

        if (!$isValidRacer) {
            return redirect()->route('tournament.cards.index')
                ->with('error', 'Card does not belong to a racer in the active tournament.');
        }

        $card->delete();

        return redirect()->route('tournament.cards.index')
            ->with('success', 'Card deleted successfully.');
    }
}
