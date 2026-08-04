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
     * Mendapatkan jumlah notifikasibelum dibaca.
     */
    public function countUnread()
    {
        $count = Auth::user()->unreadNotifications()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mendapatkan terbaru notifikasi untuk popup (terakhir 5 item).
     */
    public function recent()
    {
        $notifications = Auth::user()->notifications()
            ->latest()
            ->take(5)
            ->get();

        // Mapping icon berdasarkan tipe notifikasi
        $iconMap = [
            // Tipe tiket antrian
            'spp' => '🎫',      // Tiket SPP
            'tunai' => '💵',    // Tiket Tunai
            'tabungan' => '🏦', // Tiket Tabungan
            // Tipe sistem
            'setting_updated' => '⚙️', // Pengaturan diperbarui
            'member_new' => '👤',      // Member baru
            'loket_opened' => '🟢',    // Loket dibuka
            'loket_closed' => '🔴',    // Loket ditutup
            // Default
            'default' => '🔔',
        ];

        // Mapping label tipe tiket ke nama lengkap
        $typeLabelMap = [
            'spp' => 'SPP',
            'tunai' => 'Tunai',
            'tabungan' => 'Tabungan',
        ];

        return response()->json($notifications->map(function ($notification) use ($iconMap, $typeLabelMap) {
            $data = $notification->data ?? [];
            $type = $data['type'] ?? null;
            $ticketNumber = $data['ticket_number'] ?? null;

            // Tentukan icon berdasarkan tipe notifikasi
            $icon = $iconMap[$type] ?? $iconMap['default'];

            // Tentukan title dan message berdasarkan tipe
            if ($type === 'member_new') {
                // Notifikasi member baru (NewMemberNotification)
                $title = 'Member Baru: ' . ($data['name'] ?? 'Member');
                $message = $data['email'] ?? '';
            } elseif (isset($data['title'])) {
                // Notifikasi sistem (SystemNotification)
                $title = $data['title'];
                $message = $data['description'] ?? '';
            } else {
                // Notifikasi tiket (NewTicketNotification)
                $title = $ticketNumber
                    ? 'Tiket Baru: ' . $ticketNumber
                    : 'Notifikasi Baru';
                $message = $type
                    ? 'Tipe: ' . ($typeLabelMap[$type] ?? ucfirst($type)) . ', ' . ($data['created_at'] ?? '')
                    : '';
            }

            return [
                'id' => $notification->id,
                'icon' => $icon,
                'title' => $title,
                'message' => $message,
                'created_at' => $notification->created_at,
            ];
        }));
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
