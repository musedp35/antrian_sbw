<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with ticket statistics.
     */
    public function index()
    {
        $today = now()->startOfDay();

        // Total tickets today
        $totalToday = Ticket::whereDate('created_at', $today)->count();

        // Tickets by type (today)
        $byType = Ticket::whereDate('created_at', $today)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        // Tickets by status (today)
        $byStatus = Ticket::whereDate('created_at', $today)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Tickets per cashier (today)
        $byCashier = Ticket::whereDate('created_at', $today)
            ->selectRaw('assigned_cashier_id, COUNT(*) as total')
            ->groupBy('assigned_cashier_id')
            ->with('assignedCashier:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    // Tiket dari /members (tanpa login) tidak punya assigned_cashier_id
                    // → tampilkan "Anggota" agar jelas bahwa tiket dicetak oleh member (publik)
                    'cashier_name' => $item->assignedCashier->name ?? 'Anggota',
                    'total'        => $item->total,
                ];
            });

        // Active tickets currently being served (waiting + called)
        $activeCount = Ticket::whereIn('status', ['waiting', 'called'])->count();

        // Recent tickets
        $recentTickets = Ticket::with('assignedCashier')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalToday',
            'byType',
            'byStatus',
            'byCashier',
            'activeCount',
            'recentTickets'
        ));
    }

    /**
     * API endpoint for live stats (fetchable via Alpine.js).
     */
    public function stats()
    {
        $today = now()->startOfDay();

        $totalToday = Ticket::whereDate('created_at', $today)->count();

        $byType = Ticket::whereDate('created_at', $today)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $byStatus = Ticket::whereDate('created_at', $today)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $activeCount = Ticket::whereIn('status', ['waiting', 'called'])->count();

        return response()->json([
            'total_today'  => $totalToday,
            'active_count' => $activeCount,
            'by_type'      => [
                'spp'        => (int) ($byType['spp'] ?? 0),
                'tunai'      => (int) ($byType['tunai'] ?? 0),
                'tabungan'   => (int) ($byType['tabungan'] ?? 0),
            ],
            'by_status'    => [
                'waiting'    => (int) ($byStatus['waiting'] ?? 0),
                'called'     => (int) ($byStatus['called'] ?? 0),
                'served'     => (int) ($byStatus['served'] ?? 0),
                'cancelled'  => (int) ($byStatus['cancelled'] ?? 0),
            ],
        ]);
    }
}
