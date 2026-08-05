<?php

use App\Http\Controllers\Api\DisplayApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use Illuminate\Support\Facades\Auth;

// Root route: cek apakah user login, jika tidak redirect ke login, jika ya ke dashboard
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

// Public display page (no auth needed)
Route::get('/display', function () {
    return view('display');
})->name('display');

// Public API untuk display polling (no auth needed)
Route::get('/api/tickets/display', [DisplayApiController::class, 'index'])->name('api.display');

// Public Members — Cetak Tiket (no auth needed)
Route::get('/members', [\App\Http\Controllers\MemberController::class, 'index'])->name('members.index');
Route::post('/members/print', [\App\Http\Controllers\MemberController::class, 'print'])->name('members.print');

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard — semua role bisa akses
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/stats', [DashboardController::class, 'stats'])->name('api.dashboard.stats');

    // Profile — semua role bisa akses
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tiket — semua role yang login bisa manage antrian
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::post('/tickets/{ticket}/call', [TicketController::class, 'callAjax'])->name('tickets.call');
    Route::post('/tickets/{ticket}/recall', [TicketController::class, 'recallAjax'])->name('tickets.recall');
    Route::post('/tickets/{ticket}/serve', [TicketController::class, 'serveAjax'])->name('tickets.serve');
    Route::post('/tickets/{ticket}/cancel', [TicketController::class, 'cancelAjax'])->name('tickets.cancel');
    Route::get('/tickets/{ticket}/tts', [TicketController::class, 'tts'])->name('tickets.tts');

    // Riwayat Panggilan — semua role yang login
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');

    // Notifikasi — semua role yang login
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications/read/delete', [NotificationController::class, 'deleteRead'])->name('notifications.delete-read');
});

// Super Admin only — route grup untuk manajemen user & konfigurasi
Route::middleware(['auth', 'verified', 'role:super_admin'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Video playlist management
    Route::post('/settings/videos/upload', [SettingController::class, 'uploadVideo'])->name('settings.videos.upload');
    Route::delete('/settings/videos/delete', [SettingController::class, 'deleteVideo'])->name('settings.videos.delete');
});

require __DIR__.'/auth.php';

// Notification API Routes (web middleware - session based auth)
Route::middleware(['web', 'auth'])->prefix('api/notifications')->group(function () {
    Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'countUnread'])->name('api.notifications.unread-count');
    Route::get('/recent', [App\Http\Controllers\NotificationController::class, 'recent'])->name('api.notifications.recent');
    Route::get('/tickets/new', [App\Http\Controllers\NotificationController::class, 'getNewTickets'])->name('api.notifications.get-new-tickets');
});
