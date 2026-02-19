<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\Race;
use App\Models\BestTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisplayController extends Controller
{
    public function bestRace($slug)
    {
        $tournament = Tournament::where('slug', $slug)->firstOrFail();

        return view('display.best-race', compact('tournament'));
    }

    public function track($slug, $trackNumber)
    {
        $tournament = Tournament::where('slug', $slug)->firstOrFail();

        // Validate track number
        if ($trackNumber < 1 || $trackNumber > $tournament->track_number) {
            abort(404);
        }

        return view('display.track', compact('tournament', 'trackNumber'));
    }

    public function races($slug, Request $request)
    {
        $tournament = Tournament::where('slug', $slug)->firstOrFail();

        // Get all stages for this tournament
        $stages = Race::where('tournament_id', $tournament->id)
            ->distinct()
            ->orderBy('stage', 'desc')
            ->pluck('stage');

        // Get the selected stage or default to first available
        $selectedStage = $request->has('stage') && $request->stage !== ''
            ? (int) $request->stage
            : ($stages->first() ?? null);

        // Build race query
        $query = Race::where('tournament_id', $tournament->id)
            ->with(['racer.team', 'team']);

        if ($selectedStage) {
            $query->where('stage', $selectedStage);
        }

        $allRaces = $query->orderBy('race_no')->orderBy('track')->orderBy('lane')->get();

        // Organize races by stage → race_no → lane
        $racesByStage = [];
        foreach ($allRaces as $race) {
            $stage = $race->stage;
            $raceNo = $race->race_no ?? 0;
            if (!isset($racesByStage[$stage])) $racesByStage[$stage] = [];
            if (!isset($racesByStage[$stage][$raceNo])) $racesByStage[$stage][$raceNo] = [];
            $racesByStage[$stage][$raceNo][$race->lane] = $race;
        }

        $raceNumbers = [];
        if ($selectedStage && isset($racesByStage[$selectedStage])) {
            $raceNumbers = array_keys($racesByStage[$selectedStage]);
            sort($raceNumbers);
        }

        $maxRaceNo = max(12, $raceNumbers ? max($raceNumbers) : 0);
        $viewMode = $request->get('view', 'team');

        return view('display.races', compact(
            'tournament', 'stages', 'racesByStage',
            'selectedStage', 'raceNumbers', 'maxRaceNo', 'viewMode'
        ));
    }

    public function bestRaceSnapshot($slug)
    {
        $tournament = Tournament::where('slug', $slug)->firstOrFail();

        $nextStage = $tournament->current_stage + 1;

        // Top 6 Teams with most races in next stage
        $topTeams = Race::where('tournament_id', $tournament->id)
            ->where('stage', $nextStage)
            ->join('teams', 'races.team_id', '=', 'teams.id')
            ->select('teams.team_name', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('teams.id', 'teams.team_name')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(function ($race) {
                return [
                    'TEAM NAME' => $race->team_name,
                    'TOTAL' => $race->total
                ];
            });

        return response()->json([
            'type' => 'snapshot',
            'updatedAt' => now()->timestamp * 1000,
            'items' => $topTeams
        ]);
    }

    public function trackSnapshot($slug, $trackNumber)
    {
        $tournament = Tournament::where('slug', $slug)->firstOrFail();

        // Validate track number
        if ($trackNumber < 1 || $trackNumber > $tournament->track_number) {
            abort(404);
        }

        $currentSession = $tournament->current_bto_session;

        // Best Time Overall (BTO) for this track
        $bto = BestTime::where('tournament_id', $tournament->id)
            ->where('scope', 'OVERALL')
            ->where('track', $trackNumber)
            ->with('team')
            ->first();

        // Best Time Session for this track - get the latest session if current session has no data
        $session = BestTime::where('tournament_id', $tournament->id)
            ->where('scope', 'SESSION')
            ->where('track', $trackNumber)
            ->orderBy('session_number', 'desc')
            ->with('team')
            ->first();

        $btoData = null;
        if ($bto) {
            $btoSeconds = $this->timerToSeconds($bto->timer);
            $limitSeconds = $btoSeconds + 150; // 1:30
            $limitTimer = $this->secondsToTimer($limitSeconds);

            $btoData = [
                'TIMER' => $bto->timer,
                'TEAM' => $bto->team->team_name,
                'LIMIT' => $limitTimer
            ];
        }

        $sessionData = null;
        if ($session) {
            $sessionData = [
                'SESI' => $session->session_number,
                'TIMER' => $session->timer,
                'TEAM' => $session->team->team_name
            ];
        }

        return response()->json([
            'track' => $trackNumber,
            'bto' => $btoData,
            'sesi' => $sessionData
        ]);
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