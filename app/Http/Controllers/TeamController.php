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

        return view('teams.create', compact('tournament'));
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

        return redirect()->route('teams.index')
            ->with('success', 'Team created and added to tournament successfully.');
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

        // Load racers for this team with their cards count
        $racers = $team->racers()->withCount('cards')->latest()->get();

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

        // Check if team has racers
        if ($team->racers()->count() > 0) {
            return redirect()->route('teams.index')
                ->with('error', 'Cannot delete team. It has racers assigned. Please remove racers first.');
        }

        // Remove from tournament (delete TournamentParticipant)
        $participant->delete();

        // Optionally delete the team if it's not in any other tournament
        $otherTournaments = TournamentParticipant::where('team_id', $team->id)
            ->where('tournament_id', '!=', $tournament->id)
            ->exists();

        if (!$otherTournaments) {
            $team->delete();
            $message = 'Team deleted successfully.';
        } else {
            $message = 'Team removed from tournament successfully.';
        }

        return redirect()->route('teams.index')
            ->with('success', $message);
    }
}

