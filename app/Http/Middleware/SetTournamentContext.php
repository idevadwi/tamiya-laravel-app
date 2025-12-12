<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tournament;
use Illuminate\Support\Facades\View;

class SetTournamentContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tournamentId = session('active_tournament_id');
        
        if ($tournamentId) {
            $tournament = Tournament::find($tournamentId);
            
            if ($tournament) {
                // Share tournament to all views
                View::share('activeTournament', $tournament);
                
                // Add to request for use in controllers
                $request->merge(['activeTournament' => $tournament]);
            } else {
                // Tournament was deleted or doesn't exist, clear session
                session()->forget('active_tournament_id');
            }
        }
        
        return $next($request);
    }
}
