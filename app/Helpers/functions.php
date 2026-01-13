<?php

use App\Helpers\TournamentHelper;
use App\Models\Tournament;

if (!function_exists('getActiveTournament')) {
    /**
     * Get the currently active tournament
     *
     * @return Tournament|null
     */
    function getActiveTournament(): ?Tournament
    {
        return TournamentHelper::getActiveTournament();
    }
}

if (!function_exists('setActiveTournament')) {
    /**
     * Set the active tournament
     *
     * @param string|Tournament $tournament
     * @return bool
     */
    function setActiveTournament($tournament): bool
    {
        return TournamentHelper::setActiveTournament($tournament);
    }
}

if (!function_exists('clearActiveTournament')) {
    /**
     * Clear the active tournament
     *
     * @return void
     */
    function clearActiveTournament(): void
    {
        TournamentHelper::clearActiveTournament();
    }
}

if (!function_exists('hasActiveTournament')) {
    /**
     * Check if a tournament is currently active
     *
     * @return bool
     */
    function hasActiveTournament(): bool
    {
        return TournamentHelper::hasActiveTournament();
    }
}

