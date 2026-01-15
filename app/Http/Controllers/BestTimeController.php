<?php

namespace App\Http\Controllers;

use App\Models\BestTime;
use App\Models\Team;
use App\Models\TournamentParticipant;
use App\Helpers\AblyHelper;
use Illuminate\Http\Request;

class BestTimeController extends Controller
{
    /**
     * Display a listing of best times in the active tournament.
     */
    public function index(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Build query for best times in the active tournament
        $query = BestTime::where('tournament_id', $tournament->id)
            ->with(['team', 'tournament']);

        // Filter by track
        if ($request->has('track') && $request->track) {
            $query->where('track', $request->track);
        }

        // Filter by scope (OVERALL or SESSION)
        if ($request->has('scope') && $request->scope) {
            $query->where('scope', $request->scope);
        }

        // Filter by session number
        if ($request->has('session_number') && $request->session_number) {
            $query->where('session_number', $request->session_number);
        }

        // Filter by team
        if ($request->has('team_id') && $request->team_id) {
            $query->where('team_id', $request->team_id);
        }

        $bestTimes = $query->latest()->paginate(15);
        $bestTimes->appends($request->query());

        // Get available tracks (1 to track_number)
        $tracks = range(1, $tournament->track_number);

        // Get teams in this tournament for filtering
        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');
        $teams = Team::whereIn('id', $teamIds)->orderBy('team_name')->get();

        // Get available sessions based on tournament bto_session_number
        $sessions = range(1, $tournament->bto_session_number);

        return view('best_times.index', compact('bestTimes', 'tournament', 'tracks', 'teams', 'sessions'));
    }

    /**
     * Show the form for creating a new best time.
     */
    public function create()
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Get teams in this tournament
        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');
        $teams = Team::whereIn('id', $teamIds)->orderBy('team_name')->get();

        // Get available tracks
        $tracks = range(1, $tournament->track_number);

        return view('best_times.create', compact('tournament', 'teams', 'tracks'));
    }

    /**
     * Store a newly created best time.
     */
    public function store(Request $request)
    {
        \Log::info('BestTimeController@store - Starting', [
            'input' => $request->all()
        ]);

        $tournament = getActiveTournament();

        if (!$tournament) {
            \Log::warning('BestTimeController@store - No active tournament');
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        \Log::info('BestTimeController@store - Tournament found', [
            'tournament_id' => $tournament->id
        ]);

        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'track' => 'required|string',
            'timer' => 'required|string|regex:/^\d{1,2}:\d{2}$/', // Format: MM:SS or M:SS
            'scope' => 'required|in:OVERALL,SESSION',
            'session_number' => 'nullable|integer|min:1',
            'redirect_to' => 'nullable|string', // Optional redirect parameter
        ]);

        \Log::info('BestTimeController@store - Validation passed', [
            'validated' => $validated
        ]);

        // Verify team belongs to tournament
        \Log::info('BestTimeController@store - Checking team participation');
        $isParticipant = TournamentParticipant::where('tournament_id', $tournament->id)
            ->where('team_id', $validated['team_id'])
            ->exists();

        if (!$isParticipant) {
            \Log::warning('BestTimeController@store - Team not in tournament', [
                'team_id' => $validated['team_id']
            ]);
            return redirect()->back()
                ->with('error', 'Team does not belong to the active tournament.')
                ->withInput();
        }

        \Log::info('BestTimeController@store - Team is participant');

        // For SESSION scope, use current_bto_session if not provided
        if ($validated['scope'] === 'SESSION') {
            $sessionNumber = $validated['session_number'] ?? $tournament->current_bto_session;
            $validated['session_number'] = $sessionNumber;
        }

        // Validate: Cannot add worse time for OVERALL
        if ($validated['scope'] === 'OVERALL') {
            \Log::info('BestTimeController@store - Checking OVERALL validation');
            $existingOverall = BestTime::where('tournament_id', $tournament->id)
                ->where('track', $validated['track'])
                ->where('scope', 'OVERALL')
                ->first();

            if ($existingOverall) {
                $existingTime = $this->timerToSeconds($existingOverall->timer);
                $newTime = $this->timerToSeconds($validated['timer']);

                if ($newTime >= $existingTime) {
                    \Log::warning('BestTimeController@store - OVERALL time not better');
                    return redirect()->back()
                        ->with('error', "Cannot add OVERALL time. The timer {$validated['timer']} is not better than the existing time {$existingOverall->timer} for this track.")
                        ->withInput();
                }
            }
        }

        // Validate: Cannot add worse time for SESSION
        if ($validated['scope'] === 'SESSION') {
            \Log::info('BestTimeController@store - Checking SESSION validation');
            $existingSession = BestTime::where('tournament_id', $tournament->id)
                ->where('track', $validated['track'])
                ->where('scope', 'SESSION')
                ->where('session_number', $validated['session_number'])
                ->first();

            if ($existingSession) {
                $existingTime = $this->timerToSeconds($existingSession->timer);
                $newTime = $this->timerToSeconds($validated['timer']);

                if ($newTime >= $existingTime) {
                    \Log::warning('BestTimeController@store - SESSION time not better');
                    return redirect()->back()
                        ->with('error', "Cannot add SESSION {$validated['session_number']} time. The timer {$validated['timer']} is not better than the existing time {$existingSession->timer} for this track and session.")
                        ->withInput();
                }
            }
        }

        // Create the best time record
        \Log::info('BestTimeController@store - Creating best time record');
        try {
            $bestTime = BestTime::create([
                'tournament_id' => $tournament->id,
                'team_id' => $validated['team_id'],
                'track' => $validated['track'],
                'timer' => $validated['timer'],
                'scope' => $validated['scope'],
                'session_number' => $validated['scope'] === 'SESSION' ? $validated['session_number'] : null,
                'created_by' => auth()->id(),
            ]);
            \Log::info('BestTimeController@store - Record created successfully', [
                'best_time_id' => $bestTime->id
            ]);
        } catch (\Exception $e) {
            \Log::error('BestTimeController@store - Failed to create record', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // If this is a SESSION record, check if it beats the OVERALL record
        if ($validated['scope'] === 'SESSION') {
            \Log::info('BestTimeController@store - Updating OVERALL if better');
            $this->updateOverallIfBetter($tournament->id, $validated['team_id'], $validated['track'], $validated['timer']);
        }

        // Publish to Ably (non-blocking - won't cause 500 error if it fails)
        \Log::info('BestTimeController@store - Publishing to Ably');
        try {
            $this->publishTrackUpdate($tournament, $validated['track']);
            \Log::info('BestTimeController@store - Ably publish successful');
        } catch (\Exception $e) {
            \Log::warning('Ably publish failed after storing best time', [
                'error' => $e->getMessage(),
                'track' => $validated['track'],
                'scope' => $validated['scope']
            ]);
            // Continue execution even if Ably fails
        }

        // Redirect based on where the form was submitted from
        \Log::info('BestTimeController@store - Redirecting', [
            'redirect_route' => $validated['redirect_to'] ?? 'tournament.best_times.index'
        ]);
        $redirectRoute = $validated['redirect_to'] ?? 'tournament.best_times.index';
        return redirect()->route($redirectRoute)
            ->with('success', 'Best time recorded successfully.');
    }

    /**
     * Show the form for editing the specified best time.
     */
    public function edit(BestTime $bestTime)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify best time belongs to active tournament
        if ($bestTime->tournament_id !== $tournament->id) {
            return redirect()->route('tournament.best_times.index')
                ->with('error', 'Best time does not belong to the active tournament.');
        }

        // Get teams in this tournament
        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');
        $teams = Team::whereIn('id', $teamIds)->orderBy('team_name')->get();

        // Get available tracks
        $tracks = range(1, $tournament->track_number);

        // Get available sessions based on tournament bto_session_number
        $sessions = range(1, $tournament->bto_session_number);

        return view('best_times.edit', compact('bestTime', 'tournament', 'teams', 'tracks', 'sessions'));
    }

    /**
     * Update the specified best time.
     */
    public function update(Request $request, BestTime $bestTime)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify best time belongs to active tournament
        if ($bestTime->tournament_id !== $tournament->id) {
            return redirect()->route('tournament.best_times.index')
                ->with('error', 'Best time does not belong to the active tournament.');
        }

        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'track' => 'required|string',
            'timer' => 'required|string|regex:/^\d{1,2}:\d{2}$/', // Format: MM:SS or M:SS
            'scope' => 'required|in:OVERALL,SESSION',
            'session_number' => 'nullable|integer|min:1',
        ]);

        // Verify team belongs to tournament
        $isParticipant = TournamentParticipant::where('tournament_id', $tournament->id)
            ->where('team_id', $validated['team_id'])
            ->exists();

        if (!$isParticipant) {
            return redirect()->back()
                ->with('error', 'Team does not belong to the active tournament.')
                ->withInput();
        }

        // If scope is SESSION, session_number is required
        if ($validated['scope'] === 'SESSION' && empty($validated['session_number'])) {
            return redirect()->back()
                ->with('error', 'Session number is required for SESSION scope.')
                ->withInput();
        }

        // Validate: Cannot update to worse time for OVERALL (excluding current record)
        if ($validated['scope'] === 'OVERALL') {
            $existingOverall = BestTime::where('tournament_id', $tournament->id)
                ->where('team_id', $validated['team_id'])
                ->where('track', $validated['track'])
                ->where('scope', 'OVERALL')
                ->where('id', '!=', $bestTime->id)
                ->first();

            if ($existingOverall) {
                $existingTime = $this->timerToSeconds($existingOverall->timer);
                $newTime = $this->timerToSeconds($validated['timer']);

                if ($newTime >= $existingTime) {
                    return redirect()->back()
                        ->with('error', "Cannot update to this OVERALL time. The timer {$validated['timer']} is not better than the existing time {$existingOverall->timer} for this track.")
                        ->withInput();
                }
            }
        }

        // Validate: Cannot update to worse time for SESSION (excluding current record)
        if ($validated['scope'] === 'SESSION') {
            $existingSession = BestTime::where('tournament_id', $tournament->id)
                ->where('team_id', $validated['team_id'])
                ->where('track', $validated['track'])
                ->where('scope', 'SESSION')
                ->where('session_number', $validated['session_number'])
                ->where('id', '!=', $bestTime->id)
                ->first();

            if ($existingSession) {
                $existingTime = $this->timerToSeconds($existingSession->timer);
                $newTime = $this->timerToSeconds($validated['timer']);

                if ($newTime >= $existingTime) {
                    return redirect()->back()
                        ->with('error', "Cannot update to this SESSION {$validated['session_number']} time. The timer {$validated['timer']} is not better than the existing time {$existingSession->timer} for this track and session.")
                        ->withInput();
                }
            }
        }

        $bestTime->update([
            'team_id' => $validated['team_id'],
            'track' => $validated['track'],
            'timer' => $validated['timer'],
            'scope' => $validated['scope'],
            'session_number' => $validated['scope'] === 'SESSION' ? $validated['session_number'] : null,
            'updated_by' => auth()->id(),
        ]);

        // If this is a SESSION record, check if it beats the OVERALL record
        if ($validated['scope'] === 'SESSION') {
            $this->updateOverallIfBetter($tournament->id, $validated['team_id'], $validated['track'], $validated['timer']);
        }

        // Publish to Ably (non-blocking - won't cause 500 error if it fails)
        try {
            $this->publishTrackUpdate($tournament, $validated['track']);
        } catch (\Exception $e) {
            \Log::warning('Ably publish failed after updating best time', [
                'error' => $e->getMessage(),
                'track' => $validated['track'],
                'scope' => $validated['scope']
            ]);
            // Continue execution even if Ably fails
        }

        return redirect()->route('tournament.best_times.index')
            ->with('success', 'Best time updated successfully.');
    }

    /**
     * Remove the specified best time.
     */
    public function destroy(BestTime $bestTime)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Verify best time belongs to active tournament
        if ($bestTime->tournament_id !== $tournament->id) {
            return redirect()->route('tournament.best_times.index')
                ->with('error', 'Best time does not belong to the active tournament.');
        }

        $bestTime->delete();

        return redirect()->route('tournament.best_times.index')
            ->with('success', 'Best time deleted successfully.');
    }

    /**
     * Update OVERALL record if the given time is better.
     */
    private function updateOverallIfBetter($tournamentId, $teamId, $track, $newTimer)
    {
        // Get current OVERALL record for this tournament and track (not team-specific)
        $overallRecord = BestTime::where('tournament_id', $tournamentId)
            ->where('track', $track)
            ->where('scope', 'OVERALL')
            ->first();

        // Compare times (convert to seconds for comparison)
        $newTimeInSeconds = $this->timerToSeconds($newTimer);

        if ($overallRecord) {
            $currentTimeInSeconds = $this->timerToSeconds($overallRecord->timer);

            // If new time is better (lower), update the OVERALL record
            if ($newTimeInSeconds < $currentTimeInSeconds) {
                $overallRecord->update([
                    'timer' => $newTimer,
                    'team_id' => $teamId, // Update team_id to the team that achieved the better time
                    'updated_by' => auth()->id(),
                ]);
            }
        } else {
            // No OVERALL record exists, create one
            BestTime::create([
                'tournament_id' => $tournamentId,
                'team_id' => $teamId,
                'track' => $track,
                'timer' => $newTimer,
                'scope' => 'OVERALL',
                'session_number' => null,
                'created_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Convert timer string (MM:SS) to seconds.
     */
    private function timerToSeconds($timer)
    {
        $parts = explode(':', $timer);
        $seconds = (int) $parts[0];
        $milliseconds = (int) $parts[1];

        return ($seconds * 100) + $milliseconds; // Convert to centiseconds for comparison
    }

    /**
     * Publish track update to Ably
     */
    private function publishTrackUpdate($tournament, $trackNumber)
    {
        // Get BTO data - best overall time for this track
        $bto = BestTime::where('tournament_id', $tournament->id)
            ->where('scope', 'OVERALL')
            ->where('track', $trackNumber)
            ->orderBy('timer', 'asc') // Get the best (lowest) time
            ->with('team')
            ->first();
        
        $btoData = null;
        if ($bto) {
            $btoSeconds = AblyHelper::timerToCentiseconds($bto->timer);
            $limitSeconds = $btoSeconds + 150; // 1:30
            $limitTimer = AblyHelper::centisecondsToTimer($limitSeconds);
            
            $btoData = [
                'TIMER' => $bto->timer,
                'TEAM' => $bto->team->team_name,
                'LIMIT' => $limitTimer
            ];
        }
        
        // Get session data - get best time for current bto session
        $currentSession = $tournament->current_bto_session;
        $session = BestTime::where('tournament_id', $tournament->id)
            ->where('scope', 'SESSION')
            ->where('track', $trackNumber)
            ->where('session_number', $currentSession)
            ->orderBy('timer', 'asc') // Get the best (latest input) time for this session
            ->with('team')
            ->first();
        
        $sessionData = null;
        if ($session) {
            $sessionData = [
                'SESI' => $currentSession,
                'TIMER' => $session->timer,
                'TEAM' => $session->team->team_name
            ];
        }
        
        // Publish to Ably
        AblyHelper::publishTrack($tournament, $trackNumber, $btoData, $sessionData);
    }
}

