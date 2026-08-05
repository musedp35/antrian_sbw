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
     * Menghapus satu notifikasi.
     */
    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    /**
     * Menghapus semua notifikasi yang sudah dibaca.
     */
    public function deleteRead()
    {
        $deleted = Auth::user()->notifications()
            ->whereNotNull('read_at')
            ->delete();

        return back()->with('success', "Berhasil menghapus {$deleted} notifikasi yang sudah dibaca.");
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
     *
     * Strategi: Ambil 5 terbaru, lalu tambahkan unread prioritas di atas.
     * Return field `is_read` agar frontend bisa kasih styling berbeda.
     */
    public function recent()
    {
        // Ambil 5 unread terbaru (prioritas)
        $unread = Auth::user()->unreadNotifications()
            ->latest()
            ->take(5)
            ->get();

        // Jika unread < 5, tambahkan read terbaru sampai 5
        $notifications = $unread;
        if ($notifications->count() < 5) {
            $remaining = 5 - $notifications->count();
            $read = Auth::user()->notifications()
                ->whereNotNull('read_at')
                ->latest()
                ->take($remaining)
                ->get();
            $notifications = $notifications->merge($read)->sortByDesc('created_at')->take(5)->values();
        }

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
                'id'         => $notification->id,
                'icon'       => $icon,
                'title'      => $title,
                'message'    => $message,
                'created_at' => $notification->created_at,
                'is_read'    => $notification->read_at !== null,
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
