<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Setting;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'type',
        'status',
        'assigned_cashier_id',
        'loket',
    ];

    protected $casts = [
        'type' => 'string',
        'status' => 'string',
        'loket' => 'string',
    ];

    /**
     * Daftar loket yang tersedia.
     */
    public const LOKETS = [
        'Loket SPP',
        'Loket Tunai',
        'Loket Tabungan',
    ];

    /**
     * Get the cashier assigned to this ticket.
     */
    public function assignedCashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_cashier_id');
    }

    /**
     * Get the call logs for this ticket.
     */
    public function callLogs()
    {
        return $this->hasMany(CallLog::class);
    }

    /**
     * Generate next ticket number based on type prefix (with settings support).
     */
    public static function generateTicketNumber(string $type): string
    {
        // Get prefix from settings or use defaults
        $prefix = match ($type) {
            'spp'      => Setting::getValue('queue_prefix_spp', 'A'),
            'tunai'    => Setting::getValue('queue_prefix_tunai', 'B'),
            'tabungan' => Setting::getValue('queue_prefix_tabungan', 'C'),
            default    => 'A',
        };

        // Get last ticket number for this type
        $lastTicket = self::where('type', $type)
            ->orderByDesc('id')
            ->first();

        if ($lastTicket) {
            // Extract number from format (prefix followed by hyphen and numbers)
            $prefixLength = strlen($prefix);
            $lastNum = (int) substr($lastTicket->ticket_number, $prefixLength + 1);
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }

        return sprintf('%s-%03d', $prefix, $newNum);
    }

    /**
     * Format ticket number for display: "000 A-001" or "000 B-002".
     * For queue display and TTS.
     */
    public function getDisplayAttribute(): string
    {
        return '000 ' . $this->ticket_number;
    }
}
