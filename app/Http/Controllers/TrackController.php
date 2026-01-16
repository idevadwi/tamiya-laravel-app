<?php

namespace App\Http\Controllers;

use App\Models\BestTime;
use App\Models\Team;
use App\Models\TournamentParticipant;
use App\Helpers\AblyHelper;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    /**
     * Display track management page with BTO and session data.
     */
    public function index(Request $request)
    {
        $tournament = getActiveTournament();

        if (!$tournament) {
            return redirect()->route('home')
                ->with('error', 'Please select a tournament first.');
        }

        // Get BTO data (OVERALL scope) for each track
        $btoData = BestTime::where('tournament_id', $tournament->id)
            ->where('scope', 'OVERALL')
            ->with('team')
            ->orderBy('timer', 'asc')
            ->get()
            ->groupBy('track');

        // Get Session data for current session
        $currentSession = $tournament->current_bto_session;
        $sessionData = BestTime::where('tournament_id', $tournament->id)
            ->where('scope', 'SESSION')
            ->where('session_number', $currentSession)
            ->with('team')
            ->orderBy('timer', 'asc')
            ->get()
            ->groupBy('track');

        // Get teams in this tournament for dropdowns
        $teamIds = TournamentParticipant::where('tournament_id', $tournament->id)
            ->pluck('team_id');
        $teams = Team::whereIn('id', $teamIds)->orderBy('team_name')->get();

        // Calculate limit for each track
        $trackData = [];
        for ($track = 1; $track <= $tournament->track_number; $track++) {
            $trackKey = (string) $track;
            
            // Get BTO record for this track
            $bto = $btoData->get($trackKey)?->first();
            
            // Get Session record for this track
            $session = $sessionData->get($trackKey)?->first();
            
            // Calculate limit if BTO exists
            $limit = null;
            if ($bto) {
                $btoCentiseconds = AblyHelper::timerToCentiseconds($bto->timer);
                $limitCentiseconds = $btoCentiseconds + 150; // Add 1:50
                $limit = AblyHelper::centisecondsToTimer($limitCentiseconds);
            }
            
            $trackData[$track] = [
                'bto' => $bto,
                'session' => $session,
                'limit' => $limit,
                'current_session' => $currentSession,
            ];
        }

        // Determine column class based on track number
        $colClass = match($tournament->track_number) {
            2 => 'col-6',
            3 => 'col-4',
            4 => 'col-3',
            default => 'col-4'
        };

        return view('tracks.index', compact(
            'tournament',
            'trackData',
            'teams',
            'colClass'
        ));
    }
}
