<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan fitur Video Playlist (multi-video bergantian):
     * - video_playlist          : JSON array berisi entries video [{path, enabled, order, muted?, volume?}]
     * - video_playlist_mode     : 'sequential' (urut) atau 'shuffle' (random)
     * - video_playlist_loop     : loop seluruh playlist setelah video terakhir
     * - video_playlist_interval : jeda antar video dalam detik (0 = langsung next)
     *
     * Backward compatibility:
     * - Existing `video_url` (single) tetap dipertahankan, di-migrate jadi 1 entry di `video_playlist`
     * - Existing `video_loop` tetap, tapi sekarang artinya "loop seluruh playlist"
     *   (di-handle di frontend: per-video loop attribute = false, playlist loop = video_playlist_loop)
     */
    public function up(): void
    {
        // === 1. Default playlist: scan folder public/videos + include existing video_url ===
        $playlistEntries = [];

        // Backward compat: ambil video_url lama kalau ada
        $existingVideoUrl = DB::table('settings')->where('key', 'video_url')->value('value');
        if (!empty($existingVideoUrl)) {
            $playlistEntries[] = [
                'path'    => $existingVideoUrl,
                'enabled' => true,
                'order'   => 0,
            ];
        }

        // Auto-scan folder public/videos untuk file .mp4/.webm/.ogg
        $videosDir = public_path('videos');
        if (is_dir($videosDir)) {
            $files = scandir($videosDir);
            $order = count($playlistEntries); // lanjut dari existing
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (!in_array($ext, ['mp4', 'webm', 'ogg', 'mov'], true)) continue;
                $path = 'videos/' . $file;
                // Skip kalau sudah ada dari existing video_url (dedup)
                if (in_array($path, array_column($playlistEntries, 'path'), true)) continue;
                $playlistEntries[] = [
                    'path'    => $path,
                    'enabled' => true,
                    'order'   => $order++,
                ];
            }
        }

        // Sort by order untuk konsistensi
        usort($playlistEntries, fn($a, $b) => $a['order'] <=> $b['order']);

        // === 2. Insert 4 keys baru ===
        $newSettings = [
            [
                'key'         => 'video_playlist',
                'value'       => json_encode($playlistEntries),
                'type'        => 'json',
                'group'       => 'video',
                'description' => 'JSON array of video entries [{path, enabled, order, muted?, volume?}]. Auto-detected dari folder public/videos/.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'video_playlist_mode',
                'value'       => 'shuffle',
                'type'        => 'string',
                'group'       => 'video',
                'description' => 'Mode rotasi playlist: sequential (urut) atau shuffle (random). Default: shuffle',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'video_playlist_loop',
                'value'       => 'true',
                'type'        => 'boolean',
                'group'       => 'video',
                'description' => 'Loop seluruh playlist setelah video terakhir diputar. Default: true',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'key'         => 'video_playlist_interval',
                'value'       => '0',
                'type'        => 'number',
                'group'       => 'video',
                'description' => 'Jeda antar video dalam detik (0 = langsung next, tanpa jeda). Default: 0',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ];

        // Gunakan updateOrInsert agar idempotent (aman dijalankan berulang)
        foreach ($newSettings as $setting) {
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
                'video_playlist',
                'video_playlist_mode',
                'video_playlist_loop',
                'video_playlist_interval',
            ])
            ->delete();
    }
};
