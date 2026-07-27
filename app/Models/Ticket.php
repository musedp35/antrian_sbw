<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'type',
        'status',
        'assigned_cashier_id',
    ];

    protected $casts = [
        'type' => 'string',
        'status' => 'string',
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
     * Generate next ticket number based on type prefix.
     */
    public static function generateTicketNumber(string $type): string
    {
        $prefix = match ($type) {
            'spp' => 'A',
            'tunai' => 'B',
            'tabungan' => 'C',
            default => 'A',
        };

        // Get last ticket number for this type
        $lastTicket = self::where('type', $type)
            ->orderByDesc('id')
            ->first();

        if ($lastTicket) {
            // Extract number from "A-001" format
            $lastNum = (int) substr($lastTicket->ticket_number, strlen($prefix) + 1);
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
