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

// Public list of available video files di folder public/videos (untuk admin UI auto-detect)
Route::get('/videos/available', [SettingController::class, 'listVideosApi'])->name('api.videos.available');

// Notification API Routes - menggunakan middleware web untuk session cookie auth
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/notifications/unread-count', [NotificationController::class, 'countUnread'])->name('api.notifications.unread-count');
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('api.notifications.recent');
    Route::get('/tickets/new', [NotificationController::class, 'getNewTickets'])->name('api.notifications.get-new-tickets');
});
