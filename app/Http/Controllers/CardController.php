<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Racer;
use App\Models\Team;
use App\Models\TournamentParticipant;
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

        // Get teams in the active tournament
        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');

        // Get all teams for filter dropdown
        $teams = Team::whereIn('id', $teamIds)->orderBy('team_name')->get();

        // Get racers in those teams
        $racerIds = Racer::whereIn('team_id', $teamIds)->pluck('id');

        // Get all racers for filter dropdown
        $racers = Racer::whereIn('team_id', $teamIds)
            ->with('team')
            ->orderBy('racer_name')
            ->get();

        // Build query for cards
        $query = Card::whereIn('racer_id', $racerIds)
            ->with(['racer.team']);

        // Filter by team if provided
        if ($request->has('team_id') && $request->team_id) {
            $teamRacerIds = Racer::where('team_id', $request->team_id)->pluck('id');
            $query->whereIn('racer_id', $teamRacerIds);
        }

        // Filter by racer if provided
        if ($request->has('racer_id') && $request->racer_id) {
            $query->where('racer_id', $request->racer_id);
        }

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by card code search
        if ($request->has('search') && $request->search) {
            $query->where('card_code', 'like', '%' . $request->search . '%');
        }

        $cards = $query->latest()->paginate(10);
        $cards->appends($request->query());

        return view('cards.index', compact('cards', 'tournament', 'racers', 'teams'));
    }

    /**
     * Show the form for creating a new card.
     */
    public function create(Request $request)
    {
        $tournament = getActiveTournament();
        
        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Get teams in the active tournament
        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');

        // Get racers in those teams
        $racers = Racer::whereIn('team_id', $teamIds)
            ->with('team')
            ->orderBy('racer_name')
            ->get();

        // Pre-select racer if provided via query parameter
        $selectedRacerId = $request->get('racer_id');

        return view('cards.create', compact('tournament', 'racers', 'selectedRacerId'));
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

        // Validate racer belongs to tournament if provided
        if ($request->racer_id) {
            $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
                ->pluck('team_id');

            $isValidRacer = Racer::whereIn('team_id', $teamIds)
                ->where('id', $request->racer_id)
                ->exists();

            if (!$isValidRacer) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Selected racer does not belong to the active tournament.');
            }
        }

        $validated = $request->validate([
            'card_code' => 'required|string|max:255|unique:cards,card_code',
            'racer_id' => 'nullable|uuid|exists:racers,id',
            'coupon' => 'nullable|integer|min:0',
            'status' => 'nullable|in:ACTIVE,LOST,BANNED',
        ]);

        $card = Card::create([
            'id' => Str::uuid(),
            'card_code' => $validated['card_code'],
            'racer_id' => $validated['racer_id'] ?? null,
            'coupon' => $validated['coupon'] ?? 0,
            'status' => $validated['status'] ?? 'ACTIVE',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('cards.index')
            ->with('success', 'Card created successfully.');
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
        if ($card->racer_id) {
            $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
                ->pluck('team_id');

            $isValidRacer = Racer::whereIn('team_id', $teamIds)
                ->where('id', $card->racer_id)
                ->exists();

            if (!$isValidRacer) {
                return redirect()->route('cards.index')
                    ->with('error', 'Card does not belong to the active tournament.');
            }
        }

        return view('cards.show', compact('card', 'tournament'));
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
        if ($card->racer_id) {
            $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
                ->pluck('team_id');

            $isValidRacer = Racer::whereIn('team_id', $teamIds)
                ->where('id', $card->racer_id)
                ->exists();

            if (!$isValidRacer) {
                return redirect()->route('cards.index')
                    ->with('error', 'Card does not belong to the active tournament.');
            }
        }

        // Get teams in the active tournament
        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');

        // Get racers in those teams
        $racers = Racer::whereIn('team_id', $teamIds)
            ->with('team')
            ->orderBy('racer_name')
            ->get();

        return view('cards.edit', compact('card', 'tournament', 'racers'));
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

        // Validate racer belongs to tournament if provided
        if ($request->racer_id) {
            $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
                ->pluck('team_id');

            $isValidRacer = Racer::whereIn('team_id', $teamIds)
                ->where('id', $request->racer_id)
                ->exists();

            if (!$isValidRacer) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Selected racer does not belong to the active tournament.');
            }
        }

        $validated = $request->validate([
            'card_code' => 'required|string|max:255|unique:cards,card_code,' . $card->id,
            'racer_id' => 'nullable|uuid|exists:racers,id',
            'coupon' => 'nullable|integer|min:0',
            'status' => 'nullable|in:ACTIVE,LOST,BANNED',
        ]);

        $card->update([
            'card_code' => $validated['card_code'],
            'racer_id' => $validated['racer_id'] ?? null,
            'coupon' => $validated['coupon'] ?? $card->coupon,
            'status' => $validated['status'] ?? $card->status,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('cards.index')
            ->with('success', 'Card updated successfully.');
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
        if ($card->racer_id) {
            $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
                ->pluck('team_id');

            $isValidRacer = Racer::whereIn('team_id', $teamIds)
                ->where('id', $card->racer_id)
                ->exists();

            if (!$isValidRacer) {
                return redirect()->route('cards.index')
                    ->with('error', 'Card does not belong to the active tournament.');
            }
        }

        $card->delete();

        return redirect()->route('cards.index')
            ->with('success', 'Card deleted successfully.');
    }
}

