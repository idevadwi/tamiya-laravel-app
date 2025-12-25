<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TournamentParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasterTeamController extends Controller
{
    /**
     * Display a listing of all teams (global, not tournament-scoped).
     */
    public function index(Request $request)
    {
        // Build query for all teams
        $query = Team::withCount(['racers', 'tournamentParticipants']);

        // Filter by team name search
        if ($request->has('search') && $request->search) {
            $query->where('team_name', 'like', '%' . $request->search . '%');
        }

        $teams = $query->latest()->paginate(15);
        $teams->appends($request->query());

        return view('admin.teams.index', compact('teams'));
    }

    /**
     * Show the form for creating a new team.
     */
    public function create()
    {
        return view('admin.teams.create');
    }

    /**
     * Store a newly created team (global, not linked to any tournament).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_name' => 'required|string|max:255|unique:teams,team_name',
        ]);

        // Create the team
        $team = Team::create([
            'id' => Str::uuid(),
            'team_name' => $validated['team_name'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.teams.show', $team->id)
            ->with('success', 'Team created successfully in master data.');
    }

    /**
     * Display the specified team with all racers and tournament history.
     */
    public function show(Team $team)
    {
        // Load racers for this team
        $racers = $team->racers()->with('cards')->withCount('cards')->latest()->get();

        // Load tournament participation history
        $tournaments = $team->tournamentParticipants()
            ->with('tournament')
            ->latest()
            ->get()
            ->pluck('tournament');

        return view('admin.teams.show', compact('team', 'racers', 'tournaments'));
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Team $team)
    {
        return view('admin.teams.edit', compact('team'));
    }

    /**
     * Update the specified team.
     */
    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'team_name' => 'required|string|max:255|unique:teams,team_name,' . $team->id,
        ]);

        $team->update([
            'team_name' => $validated['team_name'],
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.teams.index')
            ->with('success', 'Team updated successfully.');
    }

    /**
     * Remove the specified team from the system (global delete).
     */
    public function destroy(Team $team)
    {
        // Check if team is in any tournaments
        $tournamentCount = $team->tournamentParticipants()->count();

        if ($tournamentCount > 0) {
            return redirect()->route('admin.teams.index')
                ->with('error', "Cannot delete team. It is currently participating in {$tournamentCount} tournament(s). Remove it from tournaments first.");
        }

        // Check if team has racers
        $racerCount = $team->racers()->count();

        if ($racerCount > 0) {
            return redirect()->route('admin.teams.index')
                ->with('error', "Cannot delete team. It has {$racerCount} racer(s). Remove or reassign racers first.");
        }

        $teamName = $team->team_name;
        $team->delete();

        return redirect()->route('admin.teams.index')
            ->with('success', "Team '{$teamName}' deleted successfully.");
    }
}
