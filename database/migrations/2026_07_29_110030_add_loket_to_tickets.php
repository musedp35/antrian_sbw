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
            // Loket tujuan tiket (nullable dulu agar aman untuk data lama)
            $table->string('loket', 32)->nullable()->after('assigned_cashier_id');
            // Index untuk query by loket
            $table->index('loket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['loket']);
            $table->dropColumn('loket');
        });
    }
};