<?php

namespace App\Http\Controllers\Tournament;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Team;
use App\Models\TournamentParticipant;
use App\Models\TournamentRacerParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    /**
     * Display a listing of teams in the active tournament.
     */
    public function index(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Build query for teams in the active tournament
        $query = Team::whereHas('tournamentParticipants', function ($q) use ($tournament) {
            $q->where('tournament_id', $tournament->id);
        })->withCount('racers');

        // Filter by team name search
        if ($request->has('search') && $request->search) {
            $query->where('team_name', 'like', '%' . $request->search . '%');
        }

        $teams = $query->latest()->paginate(10);
        $teams->appends($request->query());

        // Get active racer counts for each team in this tournament
        $activeRacerCounts = [];
        foreach ($teams as $team) {
            $activeCount = TournamentRacerParticipant::where('tournament_id', $tournament->id)
                ->where('team_id', $team->id)
                ->where('is_active', true)
                ->count();
            $activeRacerCounts[$team->id] = $activeCount;
        }

        return view('tournament.teams.index', compact('teams', 'tournament', 'activeRacerCounts'));
    }

    /**
     * Show the form for creating a new team (adding to tournament).
     */
    public function create()
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Get teams already in this tournament
        $existingTeamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');

        // Get teams NOT in this tournament with their racers
        $availableTeams = Team::whereNotIn('id', $existingTeamIds)
            ->withCount('racers')
            ->with('racers')
            ->orderBy('team_name')
            ->get();

        return view('tournament.teams.create', compact('tournament', 'availableTeams'));
    }

    /**
     * Store a newly created team and link it to the active tournament.
     */
    public function store(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        $mode = $request->input('mode', 'create');

        if ($mode === 'existing') {
            // Add existing team to tournament
            $validated = $request->validate([
                'existing_team_id' => 'required|exists:teams,id',
                'selected_racers' => 'nullable|array',
                'selected_racers.*' => 'exists:racers,id',
            ]);

            // Check if team is already in tournament
            $alreadyExists = TournamentParticipant::where('tournament_id', $tournament->id)
                ->where('team_id', $validated['existing_team_id'])
                ->exists();

            if ($alreadyExists) {
                return redirect()->route('tournament.teams.create')
                    ->with('error', 'This team is already in the tournament.');
            }

            $team = Team::findOrFail($validated['existing_team_id']);

            // Get selected racers or all racers if none selected
            $selectedRacerIds = $validated['selected_racers'] ?? $team->racers->pluck('id')->toArray();

            // Validate max racer limit
            if (count($selectedRacerIds) > $tournament->max_racer_per_team) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "You can only select up to {$tournament->max_racer_per_team} racer(s) for this tournament.");
            }

            // Validate that selected racers belong to the team
            $validRacers = $team->racers->pluck('id')->toArray();
            $invalidRacers = array_diff($selectedRacerIds, $validRacers);

            if (!empty($invalidRacers)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Some selected racers do not belong to this team.');
            }

            // Link team to tournament via TournamentParticipant
            TournamentParticipant::create([
                'id' => Str::uuid(),
                'tournament_id' => $tournament->id,
                'team_id' => $team->id,
                'created_by' => auth()->id(),
            ]);

            // Create TournamentRacerParticipant records for selected racers
            foreach ($selectedRacerIds as $racerId) {
                TournamentRacerParticipant::create([
                    'id' => Str::uuid(),
                    'tournament_id' => $tournament->id,
                    'team_id' => $team->id,
                    'racer_id' => $racerId,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]);
            }

            return redirect()->route('tournament.teams.show', $team->id)
                ->with('success', "Team added to tournament with " . count($selectedRacerIds) . " active racer(s).");
        } else {
            // Create new team
            $validated = $request->validate([
                'team_name' => 'required|string|max:255|unique:teams,team_name',
            ]);

            // Create the team
            $team = Team::create([
                'id' => Str::uuid(),
                'team_name' => $validated['team_name'],
                'created_by' => auth()->id(),
            ]);

            // Link team to tournament via TournamentParticipant
            TournamentParticipant::create([
                'id' => Str::uuid(),
                'tournament_id' => $tournament->id,
                'team_id' => $team->id,
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('tournament.teams.show', $team->id)
                ->with('success', 'Team created successfully. You can now add racers to the team.');
        }
    }

    /**
     * Display the specified team with its racers.
     */
    public function show(Team $team)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify team belongs to active tournament
        $isParticipant = TournamentParticipant::where('tournament_id', $tournament->id)
            ->where('team_id', $team->id)
            ->exists();

        if (!$isParticipant) {
            return redirect()->route('tournament.teams.index')
                ->with('error', 'Team does not belong to the active tournament.');
        }

        // Load racers for this team with their cards
        $racers = $team->racers()->with('cards')->withCount('cards')->latest()->get();

        // Load active racer status for this tournament
        $activeRacerIds = TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->where('team_id', $team->id)
            ->where('is_active', true)
            ->pluck('racer_id')
            ->toArray();

        // Get cards with card_no that are not yet assigned to any racer
        $availableCards = Card::whereNotNull('card_no')
            ->whereNull('racer_id')
            ->orderBy('card_no')
            ->get();

        return view('tournament.teams.show', compact('team', 'tournament', 'racers', 'activeRacerIds', 'availableCards'));
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Team $team)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify team belongs to active tournament
        $isParticipant = TournamentParticipant::where('tournament_id', $tournament->id)
            ->where('team_id', $team->id)
            ->exists();

        if (!$isParticipant) {
            return redirect()->route('tournament.teams.index')
                ->with('error', 'Team does not belong to the active tournament.');
        }

        return view('tournament.teams.edit', compact('team', 'tournament'));
    }

    /**
     * Update the specified team.
     */
    public function update(Request $request, Team $team)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify team belongs to active tournament
        $isParticipant = TournamentParticipant::where('tournament_id', $tournament->id)
            ->where('team_id', $team->id)
            ->exists();

        if (!$isParticipant) {
            return redirect()->route('tournament.teams.index')
                ->with('error', 'Team does not belong to the active tournament.');
        }

        $validated = $request->validate([
            'team_name' => 'required|string|max:255|unique:teams,team_name,' . $team->id,
        ]);

        $team->update([
            'team_name' => $validated['team_name'],
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('tournament.teams.index')
            ->with('success', 'Team updated successfully.');
    }

    /**
     * Remove the specified team from the tournament.
     */
    public function destroy(Team $team)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify team belongs to active tournament
        $participant = TournamentParticipant::where('tournament_id', $tournament->id)
            ->where('team_id', $team->id)
            ->first();

        if (!$participant) {
            return redirect()->route('tournament.teams.index')
                ->with('error', 'Team does not belong to the active tournament.');
        }

        // Remove team from tournament (only delete TournamentParticipant relationship)
        $participant->delete();

        // Also remove racer participants
        TournamentRacerParticipant::where('tournament_id', $tournament->id)
            ->where('team_id', $team->id)
            ->delete();

        return redirect()->route('tournament.teams.index')
            ->with('success', 'Team removed from tournament successfully.');
    }
}
