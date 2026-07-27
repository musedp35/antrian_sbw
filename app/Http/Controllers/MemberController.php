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

        return view('members.ticket', compact('ticket'));
    }
}
