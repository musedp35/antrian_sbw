<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\CallLog;
use App\Models\User;

class HistoryController extends Controller
{
    /**
     * Menampilkan riwayat panggilan dengan filter.
     */
    public function index(Request $request)
    {
        // Query dasar untuk call_logs
        $query = CallLog::with(['ticket', 'ticket.assignedCashier'])
            ->orderBy('played_at', 'desc');

        // Filter berdasarkan tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('played_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('played_at', '<=', $request->date_to);
        }

        // Filter berdasarkan status tiket
        if ($request->filled('status')) {
            $query->whereHas('ticket', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Filter berdasarkan tipe transaksi
        if ($request->filled('type')) {
            $query->whereHas('ticket', function ($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        // Filter oleh kasir
        if ($request->filled('cashier_id')) {
            $query->whereHas('ticket', function ($q) use ($request) {
                $q->where('assigned_cashier_id', $request->cashier_id);
            });
        }

        // Pagination
        $callLogs = $query->paginate(15)->withQueryString();

        // Ambil data untuk filter dropdown
        $users = User::whereNotNull('role')->get(['id', 'name']);
        $statuses = ['waiting', 'called', 'served', 'cancelled'];
        $types = ['spp', 'tunai', 'tabungan'];

        return view('history.index', compact('callLogs', 'users', 'statuses', 'types'));
    }
}
