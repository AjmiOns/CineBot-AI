<?php
// routes/api.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CinebotController;
use App\Http\Controllers\MovieFeedbackController;
use App\Http\Controllers\Admin\AdminDashboardController;

/*
|--------------------------------------------------------------------------
| CineBot API Routes
|--------------------------------------------------------------------------
| Ces routes partagent la session web (voir bootstrap/app.php),
| donc auth()->id() reflète l'utilisateur Blade connecté.
|--------------------------------------------------------------------------
*/

// ── Accessible aux invités et aux utilisateurs connectés ───────────────────
// Rate limit sur /chat : 20 messages / minute par utilisateur (ou par IP pour
// les invités) — chaque message déclenche un appel Groq + TMDB côté service IA,
// donc c'est ce qui protège la facture API et le service contre les boucles/abus.
Route::post('/chat', [CinebotController::class, 'chat'])->middleware('throttle:20,1');

Route::get('/recommendations',  [CinebotController::class, 'recommendations']);
Route::get('/trending',         [CinebotController::class, 'trending']);
Route::get('/recommendations/{userId}', [CinebotController::class, 'recommendations']);

// ── Réservé aux utilisateurs connectés (compte, historique, favoris, profil) ──
Route::middleware('auth')->group(function () {
    Route::get('/chat/history',      [CinebotController::class, 'history']);
    Route::get('/chat/sessions',     [CinebotController::class, 'sessions']);
    Route::get('/chat/sessions/{sessionId}',    [CinebotController::class, 'sessionMessages']);
    Route::delete('/chat/sessions/{sessionId}', [CinebotController::class, 'deleteSession']);

    Route::post('/movies/feedback', [MovieFeedbackController::class, 'feedback']);
    Route::get('/user/favorites',   [MovieFeedbackController::class, 'favorites']);
    Route::get('/user/watched',     [MovieFeedbackController::class, 'watched']);
    Route::get('/user/preferences', [MovieFeedbackController::class, 'preferences']);
});

// ── Réservé aux administrateurs ─────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/stats', [AdminDashboardController::class, 'stats']);
});
