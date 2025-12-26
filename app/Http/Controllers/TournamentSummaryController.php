<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\Race;
use App\Models\BestTime;
use Illuminate\Http\Request;

class TournamentSummaryController extends Controller
{
    public function index($slug)
    {
        $tournament = Tournament::where('slug', $slug)->firstOrFail();

        $currentStage = $tournament->current_stage;
        $currentSession = $tournament->current_bto_session;
        $nextStage = $currentStage + 1;
        
        $totalRaces = Race::where('tournament_id', $tournament->id)
            ->where('stage', $nextStage)
            ->max('race_no') ?? 0;

        // Best Time Overall (BTO)
        $btoOverall = BestTime::where('tournament_id', $tournament->id)
            ->where('scope', 'OVERALL')
            ->with('team')
            ->orderBy('track')
            ->get()
            ->keyBy('track');

        // Best Time Session (Current Session)
        $btoSession = BestTime::where('tournament_id', $tournament->id)
            ->where('scope', 'SESSION')
            ->where('session_number', $currentSession)
            ->with('team')
            ->orderBy('track')
            ->get()
            ->keyBy('track');

        // Top 6 Teams with most races in next stage
        $topTeams = Race::where('tournament_id', $tournament->id)
            ->where('stage', $nextStage)
            ->select('team_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('team_id')
            ->orderByDesc('total')
            ->limit(6)
            ->with('team')
            ->get();

        // Calculate Track Limits (BTO + 1:30)
        $trackLimits = [];
        for ($i = 1; $i <= $tournament->track_number; $i++) {
            if (isset($btoOverall[$i])) {
                $btoSeconds = $this->timerToSeconds($btoOverall[$i]->timer);
                $limitSeconds = $btoSeconds + 150; // 1.50 seconds (1:30)
                $trackLimits[$i] = $this->secondsToTimer($limitSeconds);
            } else {
                $trackLimits[$i] = 'N/A';
            }
        }

        return view('summary', compact(
            'tournament',
            'currentStage',
            'totalRaces',
            'btoOverall',
            'btoSession',
            'trackLimits',
            'currentSession',
            'topTeams'
        ));
    }

    private function timerToSeconds($timer)
    {
        $parts = explode(':', $timer);
        $seconds = (int) $parts[0];
        $milliseconds = (int) $parts[1];

        return ($seconds * 100) + $milliseconds;
    }

    private function secondsToTimer($totalCentiseconds)
    {
        $seconds = floor($totalCentiseconds / 100);
        $milliseconds = $totalCentiseconds % 100;

        return sprintf('%d:%02d', $seconds, $milliseconds);
    }
}
