<?php

namespace App\Http\Controllers;

use App\Models\CallLog;
use App\Models\Ticket;
use App\Services\VoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    protected VoiceService $voiceService;

    public function __construct(VoiceService $voiceService)
    {
        $this->voiceService = $voiceService;
    }

    /**
     * Display a listing of the tickets.
     */
    public function index()
    {
        $tickets = Ticket::with('assignedCashier')
            ->latest()
            ->paginate(20);

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        // Determine allowed types based on user role
        $userRole = auth()->user()->role;

        $allowedTypes = match ($userRole) {
            'admin_kasir' => [
                'tunai' => ['label' => 'B - Tunai', 'color' => 'purple'],
            ],
            'admin_spp'   => [
                'spp' => ['label' => 'A - SPP', 'color' => 'blue'],
            ],
            'admin_pj_kartu' => [
                'tabungan' => ['label' => 'C - Tabungan / Angsuran', 'color' => 'teal'],
            ],
            'super_admin' => [
                'spp'      => ['label' => 'A - SPP', 'color' => 'blue'],
                'tunai'    => ['label' => 'B - Tunai', 'color' => 'purple'],
                'tabungan' => ['label' => 'C - Tabungan / Angsuran', 'color' => 'teal'],
            ],
            default       => [
                'spp'      => ['label' => 'A - SPP', 'color' => 'blue'],
                'tunai'    => ['label' => 'B - Tunai', 'color' => 'purple'],
                'tabungan' => ['label' => 'C - Tabungan / Angsuran', 'color' => 'teal'],
            ],
        };

        return view('tickets.create', compact('allowedTypes'));
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request)
    {
        // Determine allowed types based on user role
        $userRole = auth()->user()->role;

        $allowedTypes = match ($userRole) {
            'admin_kasir' => ['tunai'],
            'admin_spp'   => ['spp'],
            'admin_pj_kartu' => ['tabungan'],
            'super_admin' => ['spp', 'tunai', 'tabungan'],
            default       => ['spp', 'tunai', 'tabungan'],
        };

        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', $allowedTypes),
        ]);

        $type = $validated['type'];
        $ticketNumber = Ticket::generateTicketNumber($type);

        // Otomatis set loket default sesuai type tiket
        $defaultLoket = match ($type) {
            'spp'      => 'Loket SPP',
            'tunai'    => 'Loket Tunai',
            'tabungan' => 'Loket Tabungan',
            default    => null,
        };

        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber,
            'type'          => $type,
            'status'        => 'waiting',
            'assigned_cashier_id' => auth()->id(),
            'loket'         => $defaultLoket,
        ]);

        // Broadcast notification for new ticket creation
        $this->broadcastTicketCreated($ticket);

        session()->flash('success', "Tiket {$ticket->ticket_number} ({$type}) berhasil dibuat.");

        return redirect()->route('tickets.index');
    }

    /**
     * Helper: broadcast notification for new ticket to all admin users.
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

    /**
     * Call the ticket (change status to 'called').
     * Returns JSON response with TTS text for frontend playback.
     */
    public function callAjax(string $id, Request $request)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->status !== 'waiting') {
            return response()->json([
                'success' => false,
                'message' => "Tiket ini sudah dalam status {$ticket->status}.",
            ], 400);
        }

        // Ambil param loket dari request, fallback ke loket yang sudah ada di ticket, lalu default by type
        $requestedLoket = $request->input('loket');
        if ($requestedLoket && in_array($requestedLoket, Ticket::LOKETS, true)) {
            $loket = $requestedLoket;
        } else {
            $loket = $ticket->loket ?? match ($ticket->type) {
                'spp'      => 'Loket SPP',
                'tunai'    => 'Loket Tunai',
                'tabungan' => 'Loket Tabungan',
                default    => null,
            };
        }

        $ttsText = $this->voiceService->generateTextForTTS(
            $ticket->ticket_number,
            $ticket->type,
            $loket
        );

        DB::transaction(function () use ($ticket, $loket) {
            $ticket->update([
                'status'            => 'called',
                'assigned_cashier_id' => auth()->id(),
                'loket'             => $loket,
            ]);

            CallLog::create([
                'ticket_id'       => $ticket->id,
                'voice_file_path' => null,
                'played_at'       => now(),
            ]);
        });

        // Broadcast notification after success
        $this->broadcastTicketCreated($ticket);

        return response()->json([
            'success'  => true,
            'ticket'   => [
                'id'              => $ticket->id,
                'ticket_number'   => $ticket->ticket_number,
                'type'            => $ticket->type,
                'status'          => 'called',
                'assigned_cashier_id' => auth()->id(),
                'loket'           => $loket,
            ],
            'tts_text' => $ttsText,
        ]);
    }

    /**
     * Mark ticket as served.
     */
    public function serveAjax(string $id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update(['status' => 'served']);

        return response()->json([
            'success' => true,
            'ticket'  => [
                'id'         => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'status'     => 'served',
            ],
        ]);
    }

    /**
     * Recall (Panggil Ulang) ticket yang sedang berstatus 'called'.
     * Tidak mengubah status tiket, hanya membuat CallLog baru dan memutar TTS.
     */
    public function recallAjax(string $id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->status !== 'called') {
            return response()->json([
                'success' => false,
                'message' => "Hanya tiket berstatus 'called' yang bisa dipanggil ulang. Status saat ini: {$ticket->status}.",
            ], 400);
        }

        // Pakai loket yang tersimpan di ticket
        $loket = $ticket->loket ?? match ($ticket->type) {
            'spp'      => 'Loket SPP',
            'tunai'    => 'Loket Tunai',
            'tabungan' => 'Loket Tabungan',
            default    => null,
        };

        // Generate ulang TTS text
        $ttsText = $this->voiceService->generateTextForTTS(
            $ticket->ticket_number,
            $ticket->type,
            $loket
        );

        // Catat ke CallLog sebagai history pemanggilan ulang
        CallLog::create([
            'ticket_id'       => $ticket->id,
            'voice_file_path' => null,
            'played_at'       => now(),
        ]);

        // Update updated_at untuk menandai aktivitas recall (berguna untuk display)
        $ticket->touch();

        return response()->json([
            'success'  => true,
            'message'  => "Tiket {$ticket->ticket_number} dipanggil ulang.",
            'ticket'   => [
                'id'              => $ticket->id,
                'ticket_number'   => $ticket->ticket_number,
                'type'            => $ticket->type,
                'status'          => $ticket->status,
                'loket'           => $loket,
            ],
            'tts_text' => $ttsText,
        ]);
    }

    /**
     * Cancel a ticket.
     */
    public function cancelAjax(string $id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'ticket'  => [
                'id'         => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'status'     => 'cancelled',
            ],
        ]);
    }

    /**
     * Mark ticket as served.
     */
    public function serve(string $id)
    {
        $ticket = Ticket::findOrFail($id);

        $ticket->update(['status' => 'served']);
        session()->flash('success', "Tiket {$ticket->ticket_number} ditandai selesai.");

        return redirect()->back();
    }

    /**
     * Cancel a ticket.
     */
    public function cancel(string $id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update(['status' => 'cancelled']);
        session()->flash('success', "Tiket {$ticket->ticket_number} dibatalkan.");

        return redirect()->back();
    }

    /**
     * Generate TTS text via API endpoint.
     */
    public function tts(string $id)
    {
        $ticket = Ticket::findOrFail($id);

        $loket = $ticket->loket ?? match ($ticket->type) {
            'spp'      => 'Loket SPP',
            'tunai'    => 'Loket Tunai',
            'tabungan' => 'Loket Tabungan',
            default    => null,
        };

        $text = $this->voiceService->generateTextForTTS(
            $ticket->ticket_number,
            $ticket->type,
            $loket
        );

        return response()->json([
            'text'  => $text,
            'lang'  => 'id-ID',
            'voice' => 'Google Bahasa Indonesia',
            'loket' => $loket,
        ]);
    }
}
