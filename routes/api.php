<?php


use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\RacerController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\TournamentController;
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


Route::apiResource('teams', TeamController::class);

Route::prefix('racers')->group(function () {
    Route::get('/', [RacerController::class, 'index']);
    Route::post('/', [RacerController::class, 'store']);
    Route::get('/{id}', [RacerController::class, 'show']);
    Route::put('/{id}', [RacerController::class, 'update']);
    Route::delete('/{id}', [RacerController::class, 'destroy']);

    // Assign team to racer
    Route::post('/{id}/assign-team', [RacerController::class, 'assignTeam']);
});

Route::prefix('cards')->group(function () {
    Route::get('/', [CardController::class, 'index']);
    Route::post('/', [CardController::class, 'store']);
    Route::get('/{id}', [CardController::class, 'show']);
    Route::put('/{id}', [CardController::class, 'update']);
    Route::delete('/{id}', [CardController::class, 'destroy']);
    Route::patch('/{id}/reassign', [CardController::class, 'reassign']);

    Route::get('/code/{card_code}', [CardController::class, 'getByCode']); //get by card_code
    Route::get('/racer/{racer_id}', [CardController::class, 'getByRacer']); //get by racer_id
});

Route::apiResource('tournaments', TournamentController::class);


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