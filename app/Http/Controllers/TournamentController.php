<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\Race;
use App\Helpers\AblyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TournamentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tournaments = Tournament::withCount([
            'participants as total_teams',
            'tournamentRacerParticipants as total_racers' => function ($query) {
                $query->where('is_active', true);
            },
            'races as race_count' => function ($query) {
                $query->whereColumn('races.stage', 'tournaments.current_stage');
            }
        ])->latest()->paginate(10);
        
        return view('tournaments.index', compact('tournaments'));
    }

    /**
     * Show tournament selector page (home)
     */
    public function selector()
    {
        $user = auth()->user();
        $tournamentsQuery = Tournament::latest();

        // Moderators should only see tournaments assigned to them (unless also admin)
        $isModeratorOnly = $user && $user->hasRole('MODERATOR') && !$user->hasRole('ADMINISTRATOR');
        if ($isModeratorOnly) {
            $tournamentsQuery->whereHas('moderators', function ($query) use ($user) {
                $query->where('tournament_moderators.user_id', $user->id);
            });
        }

        $tournaments = $tournamentsQuery->paginate(10);
        return view('home', compact('tournaments'));
    }

    /**
     * Set active tournament in session
     */
    public function select(Request $request)
    {
        $request->validate([
            'tournament_id' => 'required|exists:tournaments,id',
        ]);

        $tournament = Tournament::findOrFail($request->tournament_id);
        $user = auth()->user();

        // Moderators can only select tournaments assigned to them
        $isModeratorOnly = $user && $user->hasRole('MODERATOR') && !$user->hasRole('ADMINISTRATOR');
        if ($isModeratorOnly && !$tournament->moderators()->where('user_id', $user->id)->exists()) {
            return redirect()->route('home')
                ->with('error', 'You are not assigned to this tournament.');
        }

        if (setActiveTournament($tournament)) {
            return redirect()->route('dashboard')
                ->with('success', "Tournament '{$tournament->tournament_name}' is now active.");
        }

        return redirect()->route('home')
            ->with('error', 'Failed to set active tournament.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tournaments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tournament_name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:tournaments,slug',
            'vendor_name' => 'nullable|string|max:255',
            'current_stage' => 'nullable|integer|min:0',
            'current_bto_session' => 'nullable|integer|min:0',
            'track_number' => 'nullable|integer|min:1',
            'bto_number' => 'nullable|integer|min:1',
            'bto_session_number' => 'nullable|integer|min:0',
            'max_racer_per_team' => 'nullable|integer|min:1',
            'champion_number' => 'nullable|integer|min:1',
            'best_race_enabled' => 'nullable|boolean',
            'best_race_live_update' => 'nullable|boolean',
            'best_race_number' => 'nullable|integer|min:1',
            'persiapan_delay' => 'nullable|integer|min:0',
            'panggilan_delay' => 'nullable|integer|min:0',
            'status' => 'nullable|in:PLANNED,ACTIVE,COMPLETED,CANCELLED',
        ]);

        $tournament = Tournament::create([
            'id' => Str::uuid(),
            'tournament_name' => $validated['tournament_name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['tournament_name']),
            'vendor_name' => $validated['vendor_name'] ?? null,
            'current_stage' => $validated['current_stage'] ?? 0,
            'current_bto_session' => $validated['current_bto_session'] ?? 0,
            'track_number' => $validated['track_number'] ?? 1,
            'bto_number' => $validated['bto_number'] ?? 1,
            'bto_session_number' => $validated['bto_session_number'] ?? 0,
            'max_racer_per_team' => $validated['max_racer_per_team'] ?? 1,
            'champion_number' => $validated['champion_number'] ?? 3,
            'best_race_enabled' => isset($request->best_race_enabled) && $request->best_race_enabled == '1',
            'best_race_live_update' => isset($request->best_race_live_update) && $request->best_race_live_update == '1',
            'best_race_number' => $validated['best_race_number'] ?? 1,
            'persiapan_delay' => $validated['persiapan_delay'] ?? 2000,
            'panggilan_delay' => $validated['panggilan_delay'] ?? 1000,
            'status' => $validated['status'] ?? 'PLANNED',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('tournaments.index')
            ->with('success', 'Tournament created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tournament $tournament)
    {
        return view('tournaments.show', compact('tournament'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tournament $tournament)
    {
        return view('tournaments.edit', compact('tournament'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'tournament_name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tournaments,slug,' . $tournament->id,
            'vendor_name' => 'nullable|string|max:255',
            'current_stage' => 'nullable|integer|min:0',
            'current_bto_session' => 'nullable|integer|min:0',
            'track_number' => 'nullable|integer|min:1',
            'bto_number' => 'nullable|integer|min:1',
            'bto_session_number' => 'nullable|integer|min:0',
            'max_racer_per_team' => 'nullable|integer|min:1',
            'champion_number' => 'nullable|integer|min:1',
            'best_race_enabled' => 'nullable|boolean',
            'best_race_live_update' => 'nullable|boolean',
            'best_race_number' => 'nullable|integer|min:1',
            'persiapan_delay' => 'nullable|integer|min:0',
            'panggilan_delay' => 'nullable|integer|min:0',
            'status' => 'nullable|in:PLANNED,ACTIVE,COMPLETED,CANCELLED',
        ]);

        $updateData = array_merge(
            $validated,
            [
                'best_race_enabled' => isset($request->best_race_enabled) && $request->best_race_enabled == '1',
                'best_race_live_update' => isset($request->best_race_live_update) && $request->best_race_live_update == '1',
                'updated_by' => auth()->id()
            ]
        );

        $tournament->update($updateData);

        return redirect()->route('tournaments.index')
            ->with('success', 'Tournament updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tournament $tournament)
    {
        $tournament->delete();

        return redirect()->route('tournaments.index')
            ->with('success', 'Tournament deleted successfully.');
    }

    /**
     * Show the settings page for the specified tournament.
     */
    public function settings(Tournament $tournament)
    {
        return view('tournaments.settings', compact('tournament'));
    }

    /**
     * Update tournament settings.
     */
    public function updateSettings(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'tournament_name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tournaments,slug,' . $tournament->id,
            'vendor_name' => 'nullable|string|max:255',
            'current_stage' => 'nullable|integer|min:0',
            'current_bto_session' => 'nullable|integer|min:0',
            'track_number' => 'nullable|integer|min:1',
            'bto_number' => 'nullable|integer|min:1',
            'bto_session_number' => 'nullable|integer|min:0',
            'max_racer_per_team' => 'nullable|integer|min:1',
            'champion_number' => 'nullable|integer|min:1',
            'best_race_enabled' => 'nullable|boolean',
            'best_race_live_update' => 'nullable|boolean',
            'best_race_number' => 'nullable|integer|min:1',
            'persiapan_delay' => 'nullable|integer|min:0',
            'panggilan_delay' => 'nullable|integer|min:0',
            'status' => 'nullable|in:PLANNED,ACTIVE,COMPLETED,CANCELLED',
        ]);

        $updateData = array_merge(
            $validated,
            [
                'best_race_enabled' => isset($request->best_race_enabled) && $request->best_race_enabled == '1',
                'best_race_live_update' => isset($request->best_race_live_update) && $request->best_race_live_update == '1',
                'updated_by' => auth()->id()
            ]
        );

        $tournament->update($updateData);

        return redirect()->route('tournaments.settings', $tournament->id)
            ->with('success', 'Tournament settings updated successfully.');
    }

    /**
     * Increment the current stage for the active tournament.
     */
    public function nextStage(Request $request)
    {
        if (!hasActiveTournament()) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        $tournament = getActiveTournament();

        $tournament->current_stage = ($tournament->current_stage ?? 0) + 1;
        $tournament->updated_by = auth()->id();
        $tournament->save();

        // Refresh the active tournament in session to reflect the new stage
        setActiveTournament($tournament);

        // Publish best race update for new stage
        // $this->publishBestRaceUpdate($tournament);

        return redirect()->route('dashboard')
            ->with('success', "Moved to stage {$tournament->current_stage}.");
    }

    /**
     * Increment the current BTO session for the active tournament.
     */
    public function nextSession(Request $request)
    {
        if (!hasActiveTournament()) {
            return response()->json(['error' => 'Please select a tournament first.'], 400);
        }

        $tournament = getActiveTournament();

        $previousSession = $tournament->current_bto_session ?? 0;
        $tournament->current_bto_session = $previousSession + 1;
        $tournament->updated_by = auth()->id();
        $tournament->save();

        // Refresh the active tournament in session to reflect the new session
        setActiveTournament($tournament);

        return response()->json([
            'success' => "Successfully moved to session {$tournament->current_bto_session}."
        ]);
    }

    /**
     * Show moderators management page for the tournament.
     */
    public function moderators(Tournament $tournament)
    {
        $tournament->load('moderators.roles');
        $allModerators = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('role_name', 'MODERATOR');
        })->get();

        return view('tournaments.moderators', compact('tournament', 'allModerators'));
    }

    /**
     * Assign a moderator to the tournament.
     */
    public function assignModerator(Request $request, Tournament $tournament)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);

        // Check if user is a moderator
        if (!$user->roles()->where('role_name', 'MODERATOR')->exists()) {
            return redirect()->route('tournaments.moderators', $tournament->id)
                ->with('error', 'Selected user is not a moderator.');
        }

        // Check if already assigned
        if ($tournament->moderators()->where('user_id', $user->id)->exists()) {
            return redirect()->route('tournaments.moderators', $tournament->id)
                ->with('error', 'This moderator is already assigned to this tournament.');
        }

        \App\Models\TournamentModerator::create([
            'tournament_id' => $tournament->id,
            'user_id' => $user->id,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('tournaments.moderators', $tournament->id)
            ->with('success', 'Moderator assigned successfully.');
    }

    /**
     * Remove a moderator from the tournament.
     */
    public function removeModerator(Tournament $tournament, \App\Models\User $user)
    {
        if (!$tournament->moderators()->where('user_id', $user->id)->exists()) {
            return redirect()->route('tournaments.moderators', $tournament->id)
                ->with('error', 'This moderator is not assigned to this tournament.');
        }

        $tournament->moderators()->detach($user->id);

        return redirect()->route('tournaments.moderators', $tournament->id)
            ->with('success', 'Moderator removed successfully.');
    }

    /**
     * Publish best race update to Ably
     */
    private function publishBestRaceUpdate($tournament)
    {
        $nextStage = $tournament->current_stage + 1;
        
        // Top 6 Teams with most races in next stage
        $topTeams = Race::where('tournament_id', $tournament->id)
            ->where('stage', $nextStage)
            ->join('teams', 'races.team_id', '=', 'teams.id')
            ->select('teams.team_name', DB::raw('count(*) as total'))
            ->groupBy('teams.id', 'teams.team_name')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(function ($race) {
                return [
                    'TEAM NAME' => $race->team_name,
                    'TOTAL' => $race->total
                ];
            })
            ->toArray();
        
        // Publish to Ably
        AblyHelper::publishBestRace($tournament, $topTeams);
    }
}

