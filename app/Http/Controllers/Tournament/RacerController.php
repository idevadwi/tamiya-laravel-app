<?php

namespace App\Http\Controllers\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Racer;
use App\Models\Team;
use App\Models\TournamentParticipant;
use App\Models\TournamentRacerParticipant;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RacerController extends Controller
{
    /**
     * Display a listing of racers in the active tournament.
     */
    public function index(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Get racers participating in this tournament
        $query = Racer::whereHas('tournamentRacerParticipants', function ($q) use ($tournament) {
            $q->where('tournament_id', $tournament->id);
        })->with([
                    'team',
                    'cards',
                    'tournamentRacerParticipants' => function ($q) use ($tournament) {
                        $q->where('tournament_id', $tournament->id);
                    }
                ])->withCount('cards');

        // Filter by active status (default: show all participants)
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->whereHas('tournamentRacerParticipants', function ($q) use ($tournament) {
                    $q->where('tournament_id', $tournament->id)
                        ->where('is_active', true);
                });
            } elseif ($request->status === 'inactive') {
                $query->whereHas('tournamentRacerParticipants', function ($q) use ($tournament) {
                    $q->where('tournament_id', $tournament->id)
                        ->where('is_active', false);
                });
            }
        }

        // Filter by team if provided
        if ($request->has('team_id') && $request->team_id) {
            $query->where('team_id', $request->team_id);
        }

        // Filter by racer name search
        if ($request->has('search') && $request->search) {
            $query->where('racer_name', 'like', '%' . $request->search . '%');
        }

        $racers = $query->latest()->paginate(10);
        $racers->appends($request->query());

        // Get teams in this tournament for filter dropdown
        $teams = Team::whereHas('tournamentParticipants', function ($q) use ($tournament) {
            $q->where('tournament_id', $tournament->id);
        })->orderBy('team_name')->get();

        return view('tournament.racers.index', compact('racers', 'tournament', 'teams'));
    }

    /**
     * Show the form for creating a new racer.
     */
    public function create(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Get teams in this tournament for the dropdown
        $teams = Team::whereHas('tournamentParticipants', function ($q) use ($tournament) {
            $q->where('tournament_id', $tournament->id);
        })->orderBy('team_name')->get();

        $selectedTeamId = $request->get('team_id');

        return view('tournament.racers.create', compact('tournament', 'teams', 'selectedTeamId'));
    }

    /**
     * Toggle active status of a racer in the tournament.
     */
    public function toggleStatus(Request $request, Racer $racer)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'No active tournament'], 400);
        }

        $participant = TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->where('racer_id', $racer->id)
            ->first();

        if (!$participant) {
            // Check limits before creating new participant record
            $activeCount = TournamentRacerParticipant::where('tournament_id', $tournament->id)
                ->where('team_id', $racer->team_id)
                ->where('is_active', true)
                ->count();

            if ($activeCount >= $tournament->max_racer_per_team) {
                return response()->json(['success' => false, 'message' => "Cannot activate racer. Team limit of {$tournament->max_racer_per_team} reached."], 422);
            }

            // Create participant record with active status
            TournamentRacerParticipant::create([
                'id' => Str::uuid(),
                'tournament_id' => $tournament->id,
                'team_id' => $racer->team_id,
                'racer_id' => $racer->id,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);

            return response()->json(['success' => true, 'is_active' => true]);
        }

        // Check limits if activating
        if (!$participant->is_active) {
            $activeCount = TournamentRacerParticipant::where('tournament_id', $tournament->id)
                ->where('team_id', $participant->team_id)
                ->where('is_active', true)
                ->count();

            if ($activeCount >= $tournament->max_racer_per_team) {
                return response()->json(['success' => false, 'message' => "Cannot activate racer. Team limit of {$tournament->max_racer_per_team} reached."], 422);
            }
        }

        $participant->is_active = !$participant->is_active;
        $participant->save();

        return response()->json(['success' => true, 'is_active' => $participant->is_active]);
    }

    /**
     * Store a newly created racer in storage and add to tournament.
     */
    public function store(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return response()->json(['success' => false, 'message' => 'No active tournament'], 400);
        }

        $request->validate([
            'team_id' => 'required|exists:teams,id',
            'racer_name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
            'card_code' => 'nullable|string|unique:cards,card_code',
        ]);

        // Check max racers limit
        $activeCount = TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->where('team_id', $request->team_id)
            ->where('is_active', true)
            ->count();

        if ($activeCount >= $tournament->max_racer_per_team) {
            return response()->json(['success' => false, 'message' => "Team has reached the limit of {$tournament->max_racer_per_team} racers."], 422);
        }

        try {
            // Handle image upload
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('racers', 'public');
                $imageUrl = Storage::url($path);
            }

            // Create Racer
            $racer = Racer::create([
                'team_id' => $request->team_id,
                'racer_name' => $request->racer_name,
                'image_url' => $imageUrl,
            ]);

            // Add to Tournament
            TournamentRacerParticipant::create([
                'tournament_id' => $tournament->id,
                'team_id' => $racer->team_id,
                'racer_id' => $racer->id,
                'is_active' => true,
            ]);

            // Handle Card
            if ($request->card_code) {
                Card::create([
                    'card_code' => $request->card_code,
                    'racer_id' => $racer->id,
                    'status' => 'ACTIVE',
                    'coupon' => 0,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Racer added successfully!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error adding racer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update racer and card details.
     */
    public function updateWithCard(Request $request, Racer $racer)
    {
        $tournament = getActiveTournament();
        if (!$tournament)
            return response()->json(['success' => false, 'message' => 'No active tournament'], 400);

        $request->validate([
            'racer_name' => 'required|string|max:255',
            'card_code' => 'nullable|string',
            'card_id' => 'nullable|exists:cards,id',
        ]);

        try {
            // Update racer name
            $racer->update(['racer_name' => $request->racer_name]);

            // Handle Card Logic
            if ($request->card_code) {
                // Check if card code is taken by another card
                $existingCard = Card::where('card_code', $request->card_code)
                    ->where('id', '!=', $request->card_id)
                    ->first();

                if ($existingCard) {
                    return response()->json(['success' => false, 'message' => 'Card code already in use.'], 422);
                }

                if ($request->card_id) {
                    // Update existing card
                    $card = Card::find($request->card_id);
                    $card->update(['card_code' => $request->card_code]);
                } else {
                    // Create new card
                    Card::create([
                        'card_code' => $request->card_code,
                        'racer_id' => $racer->id,
                        'status' => 'ACTIVE',
                        'coupon' => 0,
                    ]);
                }
            } else {
                // If card code is empty but card_id exists, unassign or delete?
                // The prompt says "remove card".
                if ($request->card_id) {
                    $card = Card::find($request->card_id);
                    // Decide if we delete or just unassign. Let's unassign for safety, or delete if it's unused?
                    // Given previous logic, let's just delete as cards are usually tied to racers here.
                    $card->delete();
                }
            }

            return response()->json(['success' => true, 'message' => 'Racer updated successfully!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating racer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for editing the specified racer.
     */
    public function edit(Racer $racer)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify participation
        $participant = TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->where('racer_id', $racer->id)
            ->first();

        if (!$participant) {
            return redirect()->route('tournament.racers.index')
                ->with('error', 'Racer does not belong to the active tournament.');
        }

        // Get teams in this tournament
        $teams = Team::whereHas('tournamentParticipants', function ($q) use ($tournament) {
            $q->where('tournament_id', $tournament->id);
        })->orderBy('team_name')->get();

        return view('tournament.racers.edit', compact('racer', 'tournament', 'teams'));
    }

    /**
     * Update the specified racer in storage.
     */
    public function update(Request $request, Racer $racer)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        $request->validate([
            'racer_name' => 'required|string|max:255',
            'team_id' => 'required|exists:teams,id',
            'image' => 'nullable|image|max:2048',
        ]);

        // Verify team belongs to tournament
        $isTeamInTournament = TournamentParticipant::where('tournament_id', $tournament->id)
            ->where('team_id', $request->team_id)
            ->exists();

        if (!$isTeamInTournament) {
            return back()->withErrors(['team_id' => 'Selected team is not in this tournament.']);
        }

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($racer->image_url) {
                    $oldPath = str_replace('/storage/', '', $racer->image_url);
                    Storage::disk('public')->delete($oldPath);
                }

                $path = $request->file('image')->store('racers', 'public');
                $imageUrl = Storage::url($path);
                $racer->image_url = $imageUrl;
            }

            $racer->racer_name = $request->racer_name;
            $racer->team_id = $request->team_id;
            $racer->save();

            // Also update the team in the pivot table if it changed?
            // The pivot table `tournament_racer_participants` has `team_id`.
            $participant = TournamentRacerParticipant::where('tournament_id', $tournament->id)
                ->where('racer_id', $racer->id)
                ->first();

            if ($participant && $participant->team_id != $request->team_id) {
                $participant->team_id = $request->team_id;
                $participant->save();
            }

            return redirect()->route('tournament.racers.index')
                ->with('success', 'Racer updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error updating racer: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the racer from the tournament (and optionally delete if no history).
     */
    public function destroy(Racer $racer)
    {
        $tournament = getActiveTournament();
        if (!$tournament)
            return response()->json(['success' => false, 'message' => 'No active tournament'], 400);

        try {
            // Find participant record
            $participant = TournamentRacerParticipant::where('tournament_id', $tournament->id)
                ->where('racer_id', $racer->id)
                ->first();

            if ($participant) {
                $participant->delete();
            }

            // Optional: If we want to fully delete the racer if they have no other history
            // For now, just removing from tournament is safer and meets "Tournament Scope" requirement.

            return response()->json(['success' => true, 'message' => 'Racer removed from tournament successfully!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error removing racer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the specified racer.
     */
    public function show(Racer $racer)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify participation
        $participant = TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->where('racer_id', $racer->id)
            ->first();

        if (!$participant) {
            return redirect()->route('tournament.racers.index')
                ->with('error', 'Racer does not belong to the active tournament.');
        }

        $cards = $racer->cards()->latest()->get();

        return view('tournament.racers.show', compact('racer', 'tournament', 'cards', 'participant'));
    }
}
