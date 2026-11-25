<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DisplayApiController extends Controller
{
    public function index()
    {
        // Dapatkan tanggal hari ini dalam format Y-m-d (untuk filter)
        $today = now()->format('Y-m-d');

        // Called ticket — hanya untuk tiket hari ini (terupdate terakhir)
        $called = Ticket::where('status', 'called')
            ->whereDate('created_at', $today)  // FILTER: hari ini saja
            ->orderByDesc('updated_at')
            ->first();

        // Waiting tickets — hanya untuk tiket hari ini, diurutkan berdasarkan waktu tunggu terbanyak
        $waiting = Ticket::where('status', 'waiting')
            ->whereDate('created_at', $today)  // FILTER: hari ini saja
            ->orderBy('created_at', 'asc')
            ->take(20)
            ->get();

        // === Statistik untuk display (tambahan) ===
        $statsWaiting = Ticket::where('status', 'waiting')
            ->whereDate('created_at', $today)
            ->count();

        $statsSelesai = Ticket::where('status', 'served')
            ->whereDate('created_at', $today)
            ->count();

        $statsTotal = Ticket::whereDate('created_at', $today)
            ->count();

        // Estimasi waktu tunggu: rata-rata 5 menit per tiket (heuristic)
        // Bisa disesuaikan dengan konfigurasi dari settings nanti
        $estimasiMenit = $statsWaiting * 5;

        // === Last Called Ticket Per Kategori (SPP, Tunai, Tabungan) ===
        // Ambil tiket terakhir yang dipanggil untuk masing-masing kategori hari ini.
        // Digunakan oleh halaman Display untuk menampilkan 3 kartu kategori.
        $types = ['spp', 'tunai', 'tabungan'];
        $lastCalledByType = [];
        foreach ($types as $type) {
            $lastTicket = Ticket::where('type', $type)
                ->where('status', 'called')
                ->whereDate('created_at', $today)
                ->orderByDesc('updated_at')
                ->first();

            $lastCalledByType[$type] = $lastTicket ? [
                'id'            => $lastTicket->id,
                'ticket_number' => $lastTicket->ticket_number,
                'type'          => $lastTicket->type,
                'type_label'    => match ($lastTicket->type) {
                    'spp' => 'SPP',
                    'tunai' => 'Tunai',
                    'tabungan' => 'Tabungan',
                    default => ucfirst($lastTicket->type),
                },
                'loket'         => $lastTicket->loket,
                'updated_at'    => $lastTicket->updated_at->toIso8601String(),
            ] : null;
        }

        return response()->json([
            'called' => $called ? [
                'id'            => $called->id,
                'ticket_number' => $called->ticket_number,
                'type'          => $called->type,
                'type_label'    => match ($called->type) {
                    'spp' => 'SPP',
                    'tunai' => 'Tunai',
                    'tabungan' => 'Tabungan',
                    default => ucfirst($called->type),
                },
                'loket'         => $called->loket,
                // Penting: updated_at digunakan display untuk deteksi recall
                // (recall tidak ubah id, hanya touch updated_at)
                'updated_at'    => $called->updated_at->toIso8601String(),
            ] : null,
            // === Last Called Per Kategori (untuk 3 kartu display) ===
            'lastCalledByType' => $lastCalledByType,
            'waiting' => $waiting->map(fn($t) => [
                'id'            => $t->id,
                'ticket_number' => $t->ticket_number,
                'type'          => $t->type,
                'type_label'    => match ($t->type) {
                    'spp' => 'SPP',
                    'tunai' => 'Tunai',
                    'tabungan' => 'Tabungan',
                    default => ucfirst($t->type),
                },
                'loket'         => $t->loket,
                'created_at'    => $t->created_at->toIso8601String(),
            ]),
            // === Statistik ===
            'stats' => [
                'waiting'        => $statsWaiting,
                'selesai'        => $statsSelesai,
                'total'          => $statsTotal,
                'estimasi_menit' => $estimasiMenit,
            ],
        ]);
    }
}
