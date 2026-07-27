<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'voice_file_path',
        'played_at',
    ];

    protected $casts = [
        'played_at' => 'datetime',
    ];

    /**
     * Get the ticket that owns this call log.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Get the cashier name through the ticket relation.
     */
    public function cashier()
    {
        return $this->ticket->assignedCashier;
    }

    /**
     * Scope: order by played_at descending.
     */
    public static function boot()
    {
        parent::boot();

        // Set default order when calling without explicit ordering
    }
}
