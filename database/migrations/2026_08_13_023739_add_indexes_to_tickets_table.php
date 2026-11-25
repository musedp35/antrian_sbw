<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Composite index untuk query whereDate + where + orderBy
            $table->index(['created_at', 'type', 'status'], 'idx_tickets_datetime_status');

            // Index untuk query by status saja (common di display API)
            $table->index('status', 'idx_tickets_status');

            // Index untuk query by type (common di filter)
            $table->index('type', 'idx_tickets_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('idx_tickets_datetime_status');
            $table->dropIndex('idx_tickets_status');
            $table->dropIndex('idx_tickets_type');
        });
    }
};
