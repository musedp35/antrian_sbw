<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan 2 setting baru di group "display" / Running Text:
     * - marquee_speed         : durasi animasi (detik), semakin kecil semakin cepat
     * - marquee_letter_spacing: jarak antar huruf dalam pixel
     */
    public function up(): void
    {
        // marquee_speed: kecepatan animasi marquee dalam detik
        DB::table('settings')->insert([
            'key'         => 'marquee_speed',
            'value'       => '25',
            'type'        => 'number',
            'group'       => 'display',
            'description' => 'Kecepatan animasi running text dalam detik (5 = cepat, 60 = lambat). Default: 25',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // marquee_letter_spacing: jarak antar huruf
        DB::table('settings')->insert([
            'key'         => 'marquee_letter_spacing',
            'value'       => '0',
            'type'        => 'number',
            'group'       => 'display',
            'description' => 'Jarak antar huruf (letter-spacing) dalam pixel. Default: 0',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['marquee_speed', 'marquee_letter_spacing'])->delete();
    }
};
