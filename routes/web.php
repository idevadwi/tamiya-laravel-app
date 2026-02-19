<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LanguageController;
use App\Models\TournamentParticipant;
use App\Models\Racer;
use App\Models\Card;
use App\Models\Race;

// Language switching
Route::post('/language/switch', [LanguageController::class, 'switch'])->name('language.switch');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Tournament selector (home page) - requires auth
Route::get('/', [TournamentController::class, 'selector'])->name('home')->middleware('auth');
Route::get('/home', [TournamentController::class, 'selector'])->name('home.alt')->middleware('auth');

// Tournament selection
Route::post('/tournaments/select', [TournamentController::class, 'select'])->name('tournaments.select');

// User Management - Admin only
Route::middleware(['auth', 'role.web:ADMINISTRATOR'])->group(function () {
    Route::resource('users', UserController::class);
});

// Tournament CRUD - Admin only
Route::middleware(['auth', 'role.web:ADMINISTRATOR'])->group(function () {
    // Settings routes must be defined before resource route to avoid conflicts
    Route::get('/tournaments/{tournament}/settings', [TournamentController::class, 'settings'])->name('tournaments.settings');
    Route::put('/tournaments/{tournament}/settings', [TournamentController::class, 'updateSettings'])->name('tournaments.settings.update');

    Route::resource('tournaments', TournamentController::class);

    // Moderator assignment routes (Admin only)
    Route::get('/tournaments/{tournament}/moderators', [TournamentController::class, 'moderators'])->name('tournaments.moderators');
    Route::post('/tournaments/{tournament}/moderators', [TournamentController::class, 'assignModerator'])->name('tournaments.moderators.assign');
    Route::delete('/tournaments/{tournament}/moderators/{user}', [TournamentController::class, 'removeModerator'])->name('tournaments.moderators.remove');
});

// Dashboard - requires active tournament
Route::get('/dashboard', function () {
    if (!hasActiveTournament()) {
        return redirect()->route('home')->with('error', 'Please select a tournament first.');
    }

    $tournament = getActiveTournament();

    // Get teams in the active tournament
    $teamIds = App\Models\TournamentParticipant::where('tournament_id', $tournament->id)
        ->pluck('team_id');

    // Count total racers active in current tournament (using TournamentRacerParticipant with is_active = true)
    $totalRacers = App\Models\TournamentRacerParticipant::where('tournament_id', $tournament->id)
        ->where('is_active', true)
        ->count();

    // Get current stage
    $currentStage = $tournament->current_stage;

    // Get race count by current tournament AND current stage
    $raceCount = Race::where('tournament_id', $tournament->id)
        ->where('stage', $currentStage + 1)
        ->max('race_no') ?? 0;

    // Get best times OVERALL for each track
    $bestTimesOverall = \App\Models\BestTime::where('tournament_id', $tournament->id)
        ->where('scope', 'OVERALL')
        ->with('team')
        ->orderBy('track')
        ->orderBy('timer')
        ->get()
        ->groupBy('track');

    // Get current session number
    $currentSession = $tournament->current_bto_session;

    // Get best times for current SESSION for each track
    $bestTimesSession = \App\Models\BestTime::where('tournament_id', $tournament->id)
        ->where('scope', 'SESSION')
        ->where('session_number', $currentSession)
        ->with('team')
        ->orderBy('track')
        ->orderBy('timer')
        ->get()
        ->groupBy('track');

    return view('dashboard', compact('totalRacers', 'currentStage', 'raceCount', 'bestTimesOverall', 'currentSession', 'bestTimesSession', 'tournament'));
})->name('dashboard')->middleware(['auth', 'tournament.context']);

// Admin Master Data Routes (Global Management)
Route::middleware(['auth', 'role.web:ADMINISTRATOR'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('teams', App\Http\Controllers\Admin\MasterTeamController::class);
    Route::resource('racers', App\Http\Controllers\Admin\MasterRacerController::class);

    // Card bulk create routes
    Route::get('cards/bulk-create', [App\Http\Controllers\Admin\MasterCardController::class, 'bulkCreate'])->name('cards.bulk-create');
    Route::post('cards/bulk-create', [App\Http\Controllers\Admin\MasterCardController::class, 'bulkStore'])->name('cards.bulk-store');
    Route::post('cards/bulk-destroy', [App\Http\Controllers\Admin\MasterCardController::class, 'bulkDestroy'])->name('cards.bulk-destroy');
    Route::resource('cards', App\Http\Controllers\Admin\MasterCardController::class);

    // Scanner Device Management
    Route::resource('scanner-devices', App\Http\Controllers\Admin\ScannerDeviceController::class);
    Route::post('scanner-devices/{scanner_device}/link', [App\Http\Controllers\Admin\ScannerDeviceController::class, 'link'])->name('scanner-devices.link');
    Route::post('scanner-devices/{scanner_device}/unlink', [App\Http\Controllers\Admin\ScannerDeviceController::class, 'unlink'])->name('scanner-devices.unlink');
});

// Tournament Routes (Moderator + Admin Scope)
Route::middleware(['auth', 'role.web:ADMINISTRATOR,MODERATOR', 'tournament.context'])->prefix('tournament')->name('tournament.')->group(function () {
    Route::resource('teams', App\Http\Controllers\Tournament\TeamController::class);

    // Racer custom routes
    Route::post('/racers/{racer}/toggle-status', [App\Http\Controllers\Tournament\RacerController::class, 'toggleStatus'])->name('racers.toggle-status');
    Route::post('/racers/{racer}/update-with-card', [App\Http\Controllers\Tournament\RacerController::class, 'updateWithCard'])->name('racers.updateWithCard');
    Route::resource('racers', App\Http\Controllers\Tournament\RacerController::class);

    Route::post('/cards/bulk-destroy', [App\Http\Controllers\Tournament\CardController::class, 'bulkDestroy'])->name('cards.bulk-destroy');
    Route::resource('cards', App\Http\Controllers\Tournament\CardController::class);

    // Keep other tournament resources here for now but pointing to original controllers
    // Eventually these should be moved to Tournament namespace too
    // Note: We're not aliasing them to 'tournament.' prefix yet to avoid breaking too many things at once,
    // but the plan implied restructuring. For now, let's keep specific data (Team/Racer/Card) separate.
    // The user ONLY asked for split of Master Data (Team, Racer, Cards).

    Route::resource('races', \App\Http\Controllers\RaceController::class);
    Route::post('/races/toggle-called', [\App\Http\Controllers\RaceController::class, 'toggleCalled'])->name('races.toggleCalled');
    Route::post('/races/balance', [\App\Http\Controllers\RaceController::class, 'balanceRaces'])->name('races.balance');
    Route::post('/races/convert-to-single-track', [\App\Http\Controllers\RaceController::class, 'convertToSingleTrack'])->name('races.convertToSingleTrack');

    Route::get('/announcer', [\App\Http\Controllers\RaceController::class, 'announcer'])->name('races.announcer');

    // Best Times management
    Route::resource('best_times', \App\Http\Controllers\BestTimeController::class);

    // Track management
    Route::get('/tracks', [\App\Http\Controllers\TrackController::class, 'index'])->name('tracks.index');

    // Tournament Results management
    Route::get('/tournament-results', [\App\Http\Controllers\TournamentResultController::class, 'index'])->name('tournament_results.index');
    Route::post('/tournament-results', [\App\Http\Controllers\TournamentResultController::class, 'store'])->name('tournament_results.store');
    Route::delete('/tournament-results/{id}', [\App\Http\Controllers\TournamentResultController::class, 'destroy'])->name('tournament_results.destroy');

    // Add Race page (Card tapping)
    Route::get('/add-race', [\App\Http\Controllers\RaceController::class, 'addRacePage'])->name('races.add');
    Route::post('/add-race/submit', [\App\Http\Controllers\RaceController::class, 'addRaceByCard'])->name('races.addByCard');

    // Proceed to next stage
    Route::post('/tournaments/next-stage', [TournamentController::class, 'nextStage'])->name('tournaments.nextStage');

    // Proceed to next session
    Route::post('/tournaments/next-session', [TournamentController::class, 'nextSession'])->name('tournaments.nextSession');

});




// Public Tournament Summary
Route::get('/{slug}/summary', [App\Http\Controllers\TournamentSummaryController::class, 'index'])->name('tournament.summary');

// Public Display Routes (no auth required)
Route::prefix('{slug}')->group(function () {
    Route::get('/best-race', [App\Http\Controllers\DisplayController::class, 'bestRace'])->name('display.best-race');
    Route::get('/track-{track}', [App\Http\Controllers\DisplayController::class, 'track'])->name('display.track')
        ->where('track', '[1-9]');
    Route::get('/races', [App\Http\Controllers\DisplayController::class, 'races'])->name('display.races');
    Route::get('/stats', [App\Http\Controllers\DisplayController::class, 'stats'])->name('display.stats');
});

// API routes for real-time data
Route::prefix('api/{slug}')->group(function () {
    Route::get('/best-race/snapshot', [App\Http\Controllers\DisplayController::class, 'bestRaceSnapshot']);
    Route::get('/track-{track}/snapshot', [App\Http\Controllers\DisplayController::class, 'trackSnapshot'])
        ->where('track', '[1-9]');
});
