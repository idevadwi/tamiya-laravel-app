<?php

namespace App\Helpers;

use App\Models\Tournament;
use Illuminate\Support\Facades\Session;

class TournamentHelper
{
    /**
     * Get the currently active tournament from session
     *
     * @return Tournament|null
     */
    public static function getActiveTournament(): ?Tournament
    {
        $tournamentId = Session::get('active_tournament_id');
        
        if ($tournamentId) {
            return Tournament::find($tournamentId);
        }
        
        return null;
    }

    /**
     * Set the active tournament in session
     *
     * @param string|Tournament $tournament
     * @return bool
     */
    public static function setActiveTournament($tournament): bool
    {
        if ($tournament instanceof Tournament) {
            Session::put('active_tournament_id', $tournament->id);
            return true;
        } elseif (is_string($tournament)) {
            $tournamentModel = Tournament::find($tournament);
            if ($tournamentModel) {
                Session::put('active_tournament_id', $tournamentModel->id);
                return true;
            }
        }
        
        return false;
    }

    /**
     * Clear the active tournament from session
     *
     * @return void
     */
    public static function clearActiveTournament(): void
    {
        Session::forget('active_tournament_id');
    }

    /**
     * Check if a tournament is currently active
     *
     * @return bool
     */
    public static function hasActiveTournament(): bool
    {
        return Session::has('active_tournament_id') && self::getActiveTournament() !== null;
    }
}

