<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    /**
     * Halaman Cetak Tiket untuk Members (public).
     * Menampilkan 3 opsi tiket: SPP, Tunai, Tabungan.
     */
    public function index()
    {
        return view('members.index');
    }

    /**
     * Proses pencetakan tiket (generate nomor antrian baru).
     *
     * Mengembalikan JSON response untuk AJAX popup di frontend.
     * Includes broadcast notifikasi ke admin (sama seperti TicketController@store).
     */
    public function print(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:spp,tunai,tabungan',
        ]);

        $type = $validated['type'];

        $ticket = DB::transaction(function () use ($type) {
            $ticketNumber = Ticket::generateTicketNumber($type);
            return Ticket::create([
                'ticket_number' => $ticketNumber,
                'type'          => $type,
                'status'        => 'waiting',
            ]);
        });

        // Broadcast notifikasi ke admin (sama pattern dengan TicketController)
        $this->broadcastTicketCreated($ticket);

        // Return JSON untuk AJAX popup
        return response()->json([
            'success' => true,
            'ticket'  => [
                'id'            => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'type'          => $ticket->type,
                'created_at'    => $ticket->created_at->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Helper: broadcast notification untuk tiket baru ke semua admin users.
     * (Copy pattern dari TicketController untuk konsistensi).
     */
    private function broadcastTicketCreated(Ticket $ticket): void
    {
        $adminUsers = \App\Models\User::whereNotNull('role')->get();
        foreach ($adminUsers as $user) {
            $notification = [
                'ticket_number' => $ticket->ticket_number,
                'type'          => $ticket->type,
                'created_at'    => $ticket->created_at->format('d/m/Y H:i'),
            ];
            $user->notify(new \App\Notifications\NewTicketNotification($notification));
        }
    }
}
