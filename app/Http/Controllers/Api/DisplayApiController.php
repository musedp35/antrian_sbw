<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class DisplayApiController extends Controller
{
    public function index()
    {
        $called = Ticket::where('status', 'called')
            ->orderByDesc('updated_at')
            ->first();

        $waiting = Ticket::where('status', 'waiting')
            ->orderBy('created_at', 'asc')
            ->take(9)
            ->get();

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
                // Penting: updated_at digunakan display untuk deteksi recall
                // (recall tidak ubah id, hanya touch updated_at)
                'updated_at'    => $called->updated_at->toIso8601String(),
            ] : null,
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
            ]),
        ]);
    }
}
