<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
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
     * Format: PREFIX001 (tanpa tanda hubung) contoh: SPP001, Tunai001, Tabungan001
     * Uses atomic approach with retry logic.
     */
    public static function generateTicketNumber(string $type): string
    {
        // Get prefix from settings or use defaults
        $prefix = match ($type) {
            'spp'      => Setting::getValue('queue_prefix_spp', 'SPP'),
            'tunai'    => Setting::getValue('queue_prefix_tunai', 'Tunai'),
            'tabungan' => Setting::getValue('queue_prefix_tabungan', 'Tabungan'),
            default    => strtoupper($type),
        };

        $today = now()->format('Y-m-d');
        $maxRetries = 5;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            // Get last ticket number for this type HARI INI saja
            $lastTicket = self::where('type', $type)
                ->whereDate('created_at', $today)
                ->orderByDesc('id')
                ->first();

            if ($lastTicket) {
                // Extract number from format PREFIX-001
                // Remove prefix and dash, then get number
                $ticketWithoutPrefix = preg_replace('/^' . preg_quote($prefix, '/') . '-?/', '', $lastTicket->ticket_number);
                $lastNum = (int) $ticketWithoutPrefix;
                $newNum = $lastNum + 1;
            } else {
                // Tidak ada tiket untuk hari ini → mulai dari 001 (reset)
                $newNum = 1;
            }

            // Format: PREFIX-001
            $ticketNumber = sprintf('%s-%03d', $prefix, $newNum);

            // Verify uniqueness - if exists, increment and retry
            $exists = self::where('ticket_number', $ticketNumber)->exists();
            if (!$exists) {
                return $ticketNumber;
            }

            // Duplicate found, will retry with incremented number
            if ($attempt >= $maxRetries) {
                // Last resort: use timestamp
                return sprintf('%s%s', $prefix, time());
            }
        }

        return $ticketNumber;
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
