<?php

namespace App\Http\Controllers;

use App\Models\Racer;
use App\Models\Team;
use App\Models\TournamentParticipant;
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

        // Get teams in the active tournament
        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');

        // Get all teams for filter dropdown
        $teams = Team::whereIn('id', $teamIds)->orderBy('team_name')->get();

        // Build query for racers - include racers in tournament teams OR unassigned racers
        // Unassigned racers (team_id is null) are allowed in the tournament
        $query = Racer::where(function($q) use ($teamIds) {
            $q->whereIn('team_id', $teamIds)
              ->orWhereNull('team_id');
        })
            ->with(['team', 'cards'])
            ->withCount('cards');

        // Filter by team if provided
        if ($request->has('team_id') && $request->team_id) {
            if ($request->team_id === 'unassigned') {
                // Filter for unassigned racers (no team)
                $query->whereNull('team_id');
            } else {
                $query->where('team_id', $request->team_id);
            }
        }

        // Filter by racer name search
        if ($request->has('search') && $request->search) {
            $query->where('racer_name', 'like', '%' . $request->search . '%');
        }

        $racers = $query->latest()->paginate(10);
        $racers->appends($request->query());

        return view('racers.index', compact('racers', 'tournament', 'teams'));
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

        // Get teams in the active tournament
        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');

        $teams = Team::whereIn('id', $teamIds)->orderBy('team_name')->get();

        // Pre-select team if provided via query parameter
        $selectedTeamId = $request->get('team_id');

        return view('racers.create', compact('tournament', 'teams', 'selectedTeamId'));
    }

    /**
     * Store a newly created racer.
     */
    public function store(Request $request)
    {
        $tournament = getActiveTournament();
        
        if (!$tournament) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a tournament first.'
                ], 400);
            }
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Validate team belongs to tournament
        if ($request->team_id) {
            $isValidTeam = TournamentParticipant::where('tournament_id', $tournament->id)
                ->where('team_id', $request->team_id)
                ->exists();

            if (!$isValidTeam) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected team does not belong to the active tournament.'
                    ], 422);
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Selected team does not belong to the active tournament.');
            }

            // Check max racers per team limit
            $currentRacerCount = Racer::where('team_id', $request->team_id)->count();
            $maxRacersPerTeam = $tournament->max_racer_per_team ?? 1;

            if ($currentRacerCount >= $maxRacersPerTeam) {
                $errorMessage = "Cannot add more racers. This team has reached the maximum limit of {$maxRacersPerTeam} racer(s) per team as set in the tournament.";
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 422);
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', $errorMessage);
            }
        }

        // Convert empty string to null for team_id
        if ($request->has('team_id') && $request->team_id === '') {
            $request->merge(['team_id' => null]);
        }

        $validated = $request->validate([
            'racer_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'team_id' => 'nullable|uuid|exists:teams,id',
            'card_code' => 'nullable|string|max:255|unique:cards,card_code',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('racers', $imageName, 'public');
        }

        $racer = Racer::create([
            'id' => Str::uuid(),
            'racer_name' => $validated['racer_name'],
            'image' => $imagePath,
            'team_id' => $validated['team_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Auto-assign card if card_code is provided
        if (!empty($validated['card_code'])) {
            Card::create([
                'id' => Str::uuid(),
                'card_code' => $validated['card_code'],
                'racer_id' => $racer->id,
                'coupon' => 0,
                'status' => 'ACTIVE',
                'created_by' => auth()->id(),
            ]);
        }

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $racer->load('cards');
            $racer->loadCount('cards');
            return response()->json([
                'success' => true,
                'message' => 'Racer created successfully.',
                'racer' => $racer
            ], 201);
        }

        return redirect()->route('racers.index')
            ->with('success', 'Racer created successfully.');
    }

    /**
     * Display the specified racer with its cards.
     */
    public function show(Racer $racer)
    {
        $tournament = getActiveTournament();
        
        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify racer's team belongs to active tournament
        if ($racer->team_id) {
            $isValidTeam = TournamentParticipant::where('tournament_id', $tournament->id)
                ->where('team_id', $racer->team_id)
                ->exists();

            if (!$isValidTeam) {
                return redirect()->route('racers.index')
                    ->with('error', 'Racer does not belong to the active tournament.');
            }
        }

        // Load cards for this racer
        $cards = $racer->cards()->latest()->get();

        return view('racers.show', compact('racer', 'tournament', 'cards'));
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

        // Verify racer's team belongs to active tournament
        if ($racer->team_id) {
            $isValidTeam = TournamentParticipant::where('tournament_id', $tournament->id)
                ->where('team_id', $racer->team_id)
                ->exists();

            if (!$isValidTeam) {
                return redirect()->route('racers.index')
                    ->with('error', 'Racer does not belong to the active tournament.');
            }
        }

        // Get teams in the active tournament
        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');

        $teams = Team::whereIn('id', $teamIds)->orderBy('team_name')->get();

        return view('racers.edit', compact('racer', 'tournament', 'teams'));
    }

    /**
     * Update the specified racer.
     */
    public function update(Request $request, Racer $racer)
    {
        $tournament = getActiveTournament();
        
        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Validate team belongs to tournament if provided
        if ($request->team_id) {
            $isValidTeam = TournamentParticipant::where('tournament_id', $tournament->id)
                ->where('team_id', $request->team_id)
                ->exists();

            if (!$isValidTeam) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Selected team does not belong to the active tournament.');
            }

            // Check max racers per team limit (only if team is being changed)
            if ($racer->team_id !== $request->team_id) {
                $currentRacerCount = Racer::where('team_id', $request->team_id)->count();
                $maxRacersPerTeam = $tournament->max_racer_per_team ?? 1;

                if ($currentRacerCount >= $maxRacersPerTeam) {
                    $errorMessage = "Cannot assign racer to this team. The team has reached the maximum limit of {$maxRacersPerTeam} racer(s) per team as set in the tournament.";
                    return redirect()->back()
                        ->withInput()
                        ->with('error', $errorMessage);
                }
            }
        }

        $validated = $request->validate([
            'racer_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'team_id' => 'nullable|uuid|exists:teams,id',
        ]);

        $updateData = [
            'racer_name' => $validated['racer_name'],
            'team_id' => $validated['team_id'] ?? null,
            'updated_by' => auth()->id(),
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($racer->image && Storage::disk('public')->exists($racer->image)) {
                Storage::disk('public')->delete($racer->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('racers', $imageName, 'public');
            $updateData['image'] = $imagePath;
        }

        $racer->update($updateData);

        return redirect()->route('racers.index')
            ->with('success', 'Racer updated successfully.');
    }

    /**
     * Remove the specified racer.
     */
    public function destroy(Racer $racer)
    {
        $tournament = getActiveTournament();
        
        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify racer's team belongs to active tournament
        if ($racer->team_id) {
            $isValidTeam = TournamentParticipant::where('tournament_id', $tournament->id)
                ->where('team_id', $racer->team_id)
                ->exists();

            if (!$isValidTeam) {
                return redirect()->route('racers.index')
                    ->with('error', 'Racer does not belong to the active tournament.');
            }
        }

        // Check if racer has cards
        if ($racer->cards()->count() > 0) {
            return redirect()->route('racers.index')
                ->with('error', 'Cannot delete racer. It has cards assigned. Please remove cards first.');
        }

        // Delete image if exists
        if ($racer->image && Storage::disk('public')->exists($racer->image)) {
            Storage::disk('public')->delete($racer->image);
        }

        $racer->delete();

        return redirect()->route('racers.index')
            ->with('success', 'Racer deleted successfully.');
    }
}

