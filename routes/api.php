<?php


use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\RacerController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\TournamentController;
use App\Http\Controllers\Api\RaceController;
use App\Http\Controllers\Api\ScannerController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware(['auth:sanctum', 'role:moderator,administrator'])->group(function () {
    Route::get('/user/find', [AuthController::class, 'findUser']);
//    Route::apiResource('tournaments', TournamentController::class);
});
Route::middleware(['auth:sanctum', 'role:administrator'])->group(function () {
    Route::post('/user/{id}/promote', [AuthController::class, 'promoteUser']);
});


Route::apiResource('teams', TeamController::class)->names([
    'index' => 'api.teams.index',
    'show' => 'api.teams.show',
    'store' => 'api.teams.store',
    'update' => 'api.teams.update',
    'destroy' => 'api.teams.destroy',
]);

Route::prefix('racers')->group(function () {
    Route::get('/', [RacerController::class, 'index'])->name('api.racers.index');
    Route::post('/', [RacerController::class, 'store'])->name('api.racers.store');
    Route::get('/{id}', [RacerController::class, 'show'])->name('api.racers.show');
    Route::put('/{id}', [RacerController::class, 'update'])->name('api.racers.update');
    Route::delete('/{id}', [RacerController::class, 'destroy'])->name('api.racers.destroy');

    // Assign team to racer
    Route::post('/{id}/assign-team', [RacerController::class, 'assignTeam'])->name('api.racers.assign-team');
});

Route::prefix('cards')->group(function () {
    Route::get('/', [CardController::class, 'index'])->name('api.cards.index');
    Route::post('/', [CardController::class, 'store'])->name('api.cards.store');
    Route::get('/{id}', [CardController::class, 'show'])->name('api.cards.show');
    Route::put('/{id}', [CardController::class, 'update'])->name('api.cards.update');
    Route::delete('/{id}', [CardController::class, 'destroy'])->name('api.cards.destroy');
    Route::patch('/{id}/reassign', [CardController::class, 'reassign'])->name('api.cards.reassign');

    Route::get('/code/{card_code}', [CardController::class, 'getByCode'])->name('api.cards.get-by-code');
    Route::get('/racer/{racer_id}', [CardController::class, 'getByRacer'])->name('api.cards.get-by-racer');
});

Route::apiResource('tournaments', TournamentController::class)->names([
    'index' => 'api.tournaments.index',
    'show' => 'api.tournaments.show',
    'store' => 'api.tournaments.store',
    'update' => 'api.tournaments.update',
    'destroy' => 'api.tournaments.destroy',
]);

Route::prefix('races')->group(function () {
    Route::post('/', [RaceController::class, 'store'])->name('api.races.store');
});

// Scanner device endpoints (no auth - device identified by X-Device-Code header)
Route::prefix('scanner')->group(function () {
    Route::post('/race', [ScannerController::class, 'storeRace'])->name('api.scanner.race');
    Route::get('/heartbeat', [ScannerController::class, 'heartbeat'])->name('api.scanner.heartbeat');
});

// Health check endpoint for Docker
Route::get('/health', function () {
    try {
        // Check database connection
        DB::connection()->getPdo();

        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'database' => 'connected'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => 'Database connection failed'
        ], 503);
    }
});
