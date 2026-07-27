<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\Ticket;

class NotificationController extends Controller
{
    /**
     * Mendapatkan semua notifikasi untuk user yang login.
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()
            ->latest()
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Menandai satu notifikasi sebagai telah dibaca.
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

    /**
     * Menandai semua notifikasi sebagai telah dibaca.
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }

    /**
     * Mendapatkan jumlah notifikasi belum dibaca.
     */
    public function countUnread()
    {
        $count = Auth::user()->unreadNotifications()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mendapatkan tiket baru yang belum terlihat (untuk polling).
     */
    public function getNewTickets(Request $request)
    {
        $lastSeen = $request->input('last_seen');

        $query = Ticket::with('assignedCashier')
            ->where('status', 'called')
            ->orderBy('created_at', 'desc');

        if ($lastSeen) {
            $query->where('created_at', '>', $lastSeen);
        }

        $newTickets = $query->take(5)->get();

        return response()->json([
            'tickets' => $newTickets->map(function($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'type' => ucfirst($ticket->type),
                    'created_at' => $ticket->created_at->format('d/m/Y H:i'),
                ];
            }),
        ]);
    }
}
