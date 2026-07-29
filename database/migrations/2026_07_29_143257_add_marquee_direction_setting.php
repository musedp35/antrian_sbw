<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan setting baru di group "display" / Running Text:
     * - marquee_direction: arah animasi marquee (rtl = kanan ke kiri, ltr = kiri ke kanan)
     *
     * Default 'rtl' untuk menjaga behavior existing (running text tradisional Indonesia
     * bergerak dari kanan ke kiri). User dapat membalik ke 'ltr' jika perlu.
     */
    public function up(): void
    {
        DB::table('settings')->insert([
            'key'         => 'marquee_direction',
            'value'       => 'rtl',
            'type'        => 'string',
            'group'       => 'display',
            'description' => 'Arah animasi running text: rtl (kanan ke kiri, default) atau ltr (kiri ke kanan)',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'marquee_direction')->delete();
    }
};
