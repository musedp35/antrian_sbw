<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah setting marquee_text
        DB::table('settings')->insert([
            'key'         => 'marquee_text',
            'value'       => 'Selamat datang di Koperasi Setia Bhakti Wanita. Silakan ambil nomor antrian Anda.',
            'type'        => 'string',
            'group'       => 'display',
            'description' => 'Teks berjalan (running text) untuk pengumuman di display',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Tambah setting marquee_enabled
        DB::table('settings')->insert([
            'key'         => 'marquee_enabled',
            'value'       => 'true',
            'type'        => 'boolean',
            'group'       => 'display',
            'description' => 'Aktifkan/nonaktifkan running text di display',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['marquee_text', 'marquee_enabled'])->delete();
    }
};