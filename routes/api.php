<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public settings API endpoint (used by display page for TTS, refresh rate, etc.)
Route::get('/settings', [SettingController::class, 'apiIndex'])->name('api.settings.index');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Notification Routes
    Route::get('/notifications/unread-count', [NotificationController::class, 'countUnread'])->name('api.notifications.unread-count');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('api.notifications.recent');
    Route::get('/tickets/new', [NotificationController::class, 'getNewTickets'])->name('api.notifications.get-new-tickets');
});
