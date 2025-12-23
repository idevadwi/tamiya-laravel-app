<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TournamentParticipant;
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

        return view('teams.index', compact('teams', 'tournament'));
    }

    /**
     * Show the form for creating a new team.
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

        // Get teams NOT in this tournament
        $availableTeams = Team::whereNotIn('id', $existingTeamIds)
            ->orderBy('team_name')
            ->get();

        return view('teams.create', compact('tournament', 'availableTeams'));
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
            ]);

            // Check if team is already in tournament
            $alreadyExists = TournamentParticipant::where('tournament_id', $tournament->id)
                ->where('team_id', $validated['existing_team_id'])
                ->exists();

            if ($alreadyExists) {
                return redirect()->route('teams.create')
                    ->with('error', 'This team is already in the tournament.');
            }

            $team = Team::findOrFail($validated['existing_team_id']);

            // Link team to tournament via TournamentParticipant
            TournamentParticipant::create([
                'id' => Str::uuid(),
                'tournament_id' => $tournament->id,
                'team_id' => $team->id,
                'created_by' => auth()->id(),
            ]);

            return redirect()->route('teams.show', $team->id)
                ->with('success', 'Team added to tournament successfully.');
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

            return redirect()->route('teams.show', $team->id)
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
            return redirect()->route('teams.index')
                ->with('error', 'Team does not belong to the active tournament.');
        }

        // Load racers for this team with their cards
        $racers = $team->racers()->with('cards')->withCount('cards')->latest()->get();

        return view('teams.show', compact('team', 'tournament', 'racers'));
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
            return redirect()->route('teams.index')
                ->with('error', 'Team does not belong to the active tournament.');
        }

        return view('teams.edit', compact('team', 'tournament'));
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
            return redirect()->route('teams.index')
                ->with('error', 'Team does not belong to the active tournament.');
        }

        $validated = $request->validate([
            'team_name' => 'required|string|max:255|unique:teams,team_name,' . $team->id,
        ]);

        $team->update([
            'team_name' => $validated['team_name'],
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('teams.index')
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
            return redirect()->route('teams.index')
                ->with('error', 'Team does not belong to the active tournament.');
        }

        // Remove team from tournament (only delete TournamentParticipant relationship)
        $participant->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team removed from tournament successfully. The team can be added to other tournaments.');
    }
}

