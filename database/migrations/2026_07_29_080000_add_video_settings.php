<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan settings video display ke table `settings`.
     * Settings ini digunakan oleh halaman Display Antrian untuk
     * menampilkan pemutar video promosi koperasi.
     */
    public function up(): void
    {
        $videoSettings = [
            [
                'key'         => 'video_enabled',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'video',
                'description' => 'Aktifkan pemutar video di halaman display',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'video_url',
                'value'       => 'videos/TokokuSBW.mp4',
                'type'        => 'string',
                'group'       => 'video',
                'description' => 'Path/URL file video promosi (relatif dari folder public)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'video_autoplay',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'video',
                'description' => 'Auto-play video saat halaman dimuat',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'video_muted',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'video',
                'description' => 'Mute audio video (wajib true untuk auto-play di Chrome/Firefox)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'video_loop',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'video',
                'description' => 'Loop video secara terus-menerus',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'video_volume',
                'value'       => '100',
                'type'        => 'number',
                'group'       => 'video',
                'description' => 'Volume audio video (0-100)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'video_poster',
                'value'       => null,
                'type'        => 'string',
                'group'       => 'video',
                'description' => 'Gambar poster yang ditampilkan sebelum video diputar (opsional)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        // Gunakan updateOrInsert agar idempotent (aman dijalankan berulang)
        foreach ($videoSettings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')
            ->whereIn('key', [
                'video_enabled',
                'video_url',
                'video_autoplay',
                'video_muted',
                'video_loop',
                'video_volume',
                'video_poster',
            ])
            ->delete();
    }
};
