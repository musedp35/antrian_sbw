<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display settings grouped by category.
     */
    public function index()
    {
        $groups = [
            'tts' => ['title' => 'Konfigurasi Suara (TTS)', 'settings' => []],
            'display' => ['title' => 'Konfigurasi Display', 'settings' => []],
            'marquee' => ['title' => 'Running Text / Marquee', 'settings' => []],
            'video' => ['title' => 'Konfigurasi Video Display', 'settings' => []],
            'general' => ['title' => 'Konfigurasi Umum', 'settings' => []],
        ];

        // Custom label yang lebih deskriptif untuk beberapa key
        $customLabels = [
            'marquee_text'             => 'Teks Running Text',
            'marquee_enabled'          => 'Aktifkan Running Text',
            'marquee_speed'            => 'Kecepatan Running Text (detik)',
            'marquee_letter_spacing'   => 'Jarak Antar Huruf (pixel)',
            'marquee_direction'        => 'Arah Alur Teks (Direction)',
            'tts_rate'                 => 'Kecepatan bicara TTS (0.1 - 2.0)',
            'tts_volume'               => 'Volume TTS (0 - 100)',
            'tts_lang'                 => 'Bahasa TTS (id-ID/en-US)',
            'video_url'                => 'URL Video Promosi (legacy)',
            'video_enabled'            => 'Aktifkan Video',
            'video_autoplay'           => 'Auto-play video',
            'video_muted'              => 'Mute video (default global)',
            'video_loop'               => 'Loop video individual (legacy)',
            'video_volume'             => 'Volume video (default global, 0-100)',
            'video_poster'             => 'Poster image (opsional)',
            'video_playlist'           => 'Playlist Video',
            'video_playlist_mode'      => 'Mode Rotasi Playlist',
            'video_playlist_loop'      => 'Loop seluruh playlist',
            'video_playlist_interval'  => 'Jeda antar video (detik)',
        ];

        // Min/max untuk input number tertentu (UX: batasi nilai di form)
        $inputAttrs = [
            'marquee_speed'              => 'min="5" max="120" step="1"',         // 5-120 detik
            'marquee_letter_spacing'     => 'min="0" max="20" step="1"',          // 0-20 px
            'tts_rate'                   => 'min="0.1" max="2.0" step="0.1"',
            'tts_volume'                 => 'min="0" max="100" step="1"',
            'video_volume'               => 'min="0" max="100" step="1"',
            'video_playlist_interval'    => 'min="0" max="60" step="1"',          // 0-60 detik
            'display_refresh_rate'       => 'min="500" max="10000" step="100"',
        ];

        // Pilih select untuk setting yang punya opsi terbatas
        $selectOptions = [
            'marquee_direction' => [
                'rtl' => 'Kanan ke Kiri (RTL, default)',
                'ltr' => 'Kiri ke Kanan (LTR)',
            ],
            'video_playlist_mode' => [
                'sequential' => 'Sequential (urut 1 → 2 → 3)',
                'shuffle'    => 'Shuffle (random)',
            ],
        ];

        // PERFORMANCE FIX: Load all settings at once (avoid N+1 query)
        $settings = Setting::orderBy('group')->orderBy('key')->get();
        $settingsByKey = $settings->keyBy('key');

        // Build a values cache to avoid repeated Setting::getValue() DB calls
        $valuesCache = [];
        foreach ($settings as $s) {
            $valuesCache[$s->key] = $this->castValue($s->value, $s->type);
        }

        foreach ($settings as $s) {
            $val = $valuesCache[$s->key] ?? null;
            // Jika group "display" tapi key adalah marquee_*, pindahkan ke group "marquee"
            $groupKey = $s->group;
            if ($groupKey === 'display' && str_starts_with($s->key, 'marquee_')) {
                $groupKey = 'marquee';
            }
            $groups[$groupKey]['settings'][] = [
                'key'            => $s->key,
                'value'          => is_bool($val) ? ($val ? 'true' : 'false') : $val,
                'type'           => $s->type,
                'desc'           => $s->description,
                'label'          => $customLabels[$s->key] ?? null,
                'input_attrs'    => $inputAttrs[$s->key] ?? 'min="0" max="200" step="0.1"',
                'select_options' => $selectOptions[$s->key] ?? null,
            ];
        }

        return view('settings.index', compact('groups'));
    }

    /**
     * Cast setting value based on type.
     *
     * Supported types:
     * - 'number'  → int
     * - 'boolean' → bool
     * - 'json'    → array (for display in form)
     * - 'string'  → string (default)
     */
    private function castValue(?string $value, string $type): mixed
    {
        return match ($type) {
            'number'  => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($value ?? '[]', true) ?? [],
            default   => $value,
        };
    }

    /**
     * Update settings via bulk form submission.
     *
     * Mendukung:
     * - Scalar values (string/number/boolean) via `settings[*][key]/value`
     * - JSON values via `settings_json[KEY]` untuk tipe json
     *   (mis. video_playlist) — akan di-decode, divalidasi, lalu di-encode lagi
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings.*.key'        => 'required|string',
            'settings.*.value'      => 'nullable|string',
            'settings_json'         => 'nullable|array',
            'settings_json.*'       => 'nullable', // akan divalidasi per-key di bawah
        ]);

        DB::beginTransaction();
        try {
            // PERFORMANCE FIX: Load all relevant settings once
            $keys = array_column($validated['settings'], 'key');

            // Tambahkan key JSON ke query kalau ada
            $jsonKeys = array_keys($validated['settings_json'] ?? []);
            $allKeys = array_unique(array_merge($keys, $jsonKeys));

            $existingSettings = Setting::whereIn('key', $allKeys)->get()->keyBy('key');

            // Batas nilai untuk setting number tertentu (server-side guard)
            $numberLimits = [
                'marquee_speed'          => [5, 120],
                'marquee_letter_spacing' => [0, 20],
                'video_playlist_interval' => [0, 60],
            ];

            // Whitelist nilai yang diperbolehkan untuk setting select tertentu
            $stringWhitelist = [
                'marquee_direction'    => ['rtl', 'ltr'],
                'video_playlist_mode'  => ['sequential', 'shuffle'],
            ];

            // === 1. Proses scalar settings (existing flow) ===
            foreach ($validated['settings'] as $item) {
                $setting = $existingSettings[$item['key']] ?? null;
                if (!$setting) continue;
                // Skip kalau key ini akan diproses via JSON handler
                if ($setting->type === 'json') continue;

                $currentVal = $this->castValue($setting->value, $setting->type);
                if (empty($item['value']) && !is_bool($currentVal)) {
                    continue;
                }

                if ($setting->type === 'number' && isset($numberLimits[$setting->key])) {
                    [$min, $max] = $numberLimits[$setting->key];
                    $num = (int) $item['value'];
                    $item['value'] = (string) max($min, min($max, $num));
                }

                if ($setting->type === 'string' && isset($stringWhitelist[$setting->key])) {
                    if (!in_array($item['value'], $stringWhitelist[$setting->key], true)) {
                        continue;
                    }
                }

                $setting->update(['value' => $item['value']]);
            }

            // === 2. Proses JSON settings (video_playlist, dll) ===
            foreach (($validated['settings_json'] ?? []) as $jsonKey => $rawValue) {
                $setting = $existingSettings[$jsonKey] ?? null;
                if (!$setting || $setting->type !== 'json') continue;

                // Validasi khusus untuk video_playlist
                if ($jsonKey === 'video_playlist') {
                    $playlist = $this->normalizeVideoPlaylist($rawValue);
                    $setting->update(['value' => json_encode($playlist)]);
                }
            }

            DB::commit();

            // Clear config cache so changes take effect
            \Illuminate\Support\Facades\Artisan::call('config:clear');

            session()->flash('success', 'Pengaturan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }

        return redirect()->route('settings.index');
    }

    /**
     * API endpoint for frontend (display) to retrieve settings.
     *
     * Decode JSON values ke array agar frontend langsung pakai (tidak perlu JSON.parse lagi).
     * - video_playlist (json) → array
     * - scalar (string/number/boolean) → string (seperti sebelumnya, backward compat)
     */
    public function apiIndex()
    {
        $settings = Setting::orderBy('key')->get();
        $result = [];
        foreach ($settings as $s) {
            $result[$s->key] = match ($s->type) {
                'json'    => json_decode($s->value ?? '[]', true) ?? [],
                'boolean' => filter_var($s->value, FILTER_VALIDATE_BOOLEAN),
                'number'  => (int) $s->value,
                default   => $s->value,
            };
        }
        return response()->json($result);
    }

    /**
     * Normalize & validate playlist payload dari form.
     *
     * Accept multiple formats dari form:
     * 1. JSON string: '{"path":"...","enabled":true}'  (single entry)
     * 2. JSON string array: '[{...},{...}]'             (full playlist)
     * 3. Form-encoded: { 0: {path:.., enabled:..}, 1: ... }
     *
     * Output: array of {path, enabled, order, muted?, volume?}
     * - Default muted/volume = null (fallback ke global setting)
     * - order auto-di-reset sequential (0,1,2,...)
     * - Filter hanya yang path valid (file exists di folder videos)
     */
    private function normalizeVideoPlaylist(mixed $raw): array
    {
        // Decode kalau JSON string
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        if (!is_array($raw)) {
            return [];
        }

        // Kalau 1 entry bukan indexed array, wrap jadi list
        if (array_keys($raw) !== range(0, count($raw) - 1)) {
            $raw = [$raw];
        }

        $normalized = [];
        $order = 0;
        $videosDir = public_path('videos');
        foreach ($raw as $entry) {
            if (!is_array($entry) || empty($entry['path'])) continue;

            $path = trim((string) $entry['path']);
            // Skip kalau file tidak ada di folder public/videos
            $absolutePath = $videosDir . DIRECTORY_SEPARATOR . basename($path);
            if (!file_exists($absolutePath)) continue;

            $item = [
                'path'    => 'videos/' . basename($path), // normalize path
                'enabled' => isset($entry['enabled']) ? (bool) $entry['enabled'] : true,
                'order'   => $order++,
            ];

            // Per-video override (opsional)
            if (isset($entry['muted']) && $entry['muted'] !== '' && $entry['muted'] !== null) {
                $item['muted'] = filter_var($entry['muted'], FILTER_VALIDATE_BOOLEAN);
            }
            if (isset($entry['volume']) && $entry['volume'] !== '' && $entry['volume'] !== null) {
                $vol = (int) $entry['volume'];
                $item['volume'] = max(0, min(100, $vol)); // clamp 0-100
            }

            $normalized[] = $item;
        }

        return $normalized;
    }

    /**
     * List available video files di folder public/videos.
     *
     * Return info lengkap agar admin UI bisa cek file size & durasi (estimasi).
     * Format: [{ name, path, size_bytes, size_human, modified }]
     */
    public function listVideos(): array
    {
        $videosDir = public_path('videos');
        if (!is_dir($videosDir)) {
            return [];
        }

        $files = scandir($videosDir);
        $list = [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['mp4', 'webm', 'ogg', 'mov'], true)) continue;

            $absolute = $videosDir . DIRECTORY_SEPARATOR . $file;
            $sizeBytes = file_exists($absolute) ? filesize($absolute) : 0;

            $list[] = [
                'name'       => $file,
                'path'       => 'videos/' . $file,
                'size_bytes' => $sizeBytes,
                'size_human' => $this->humanFileSize($sizeBytes),
                'modified'   => file_exists($absolute) ? filemtime($absolute) : null,
            ];
        }

        // Sort by name (ascending) untuk konsistensi
        usort($list, fn($a, $b) => strcmp($a['name'], $b['name']));
        return $list;
    }

    /**
     * Upload video file ke folder public/videos.
     *
     * Validasi:
     * - File harus ada dan valid (mimes:mp4,webm,ogg,mov)
     * - Maks 200MB (display device biasanya punya storage terbatas)
     * - Auto-append ke video_playlist jika belum ada
     */
    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,webm,ogg,mov|max:204800', // 200MB dalam KB
        ], [
            'video.required' => 'Pilih file video terlebih dahulu.',
            'video.file'     => 'File yang diupload tidak valid.',
            'video.mimes'    => 'Format video harus: mp4, webm, ogg, atau mov.',
            'video.max'      => 'Ukuran file maksimal 200MB.',
        ]);

        $file = $request->file('video');
        $videosDir = public_path('videos');

        // Pastikan folder ada
        if (!is_dir($videosDir)) {
            mkdir($videosDir, 0755, true);
        }

        // Generate nama file unik agar tidak overwrite (preserve original name + timestamp)
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension    = $file->getClientOriginalExtension();
        $safeName     = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalName);
        $filename     = $safeName . '_' . time() . '.' . $extension;

        $file->move($videosDir, $filename);

        // Auto-append ke video_playlist jika belum ada
        $playlistJson = Setting::getValue('video_playlist', []);
        if (!is_array($playlistJson)) {
            $playlistJson = [];
        }

        $newPath = 'videos/' . $filename;
        $alreadyExists = false;
        foreach ($playlistJson as $entry) {
            if (isset($entry['path']) && $entry['path'] === $newPath) {
                $alreadyExists = true;
                break;
            }
        }

        if (!$alreadyExists) {
            $playlistJson[] = [
                'path'    => $newPath,
                'enabled' => true,
                'order'   => count($playlistJson),
            ];

            // Simpan kembali
            Setting::where('key', 'video_playlist')->update([
                'value'      => json_encode($playlistJson),
                'updated_at' => now(),
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Video berhasil diupload.',
                'video'   => [
                    'name'       => $filename,
                    'path'       => $newPath,
                    'size_bytes' => filesize($videosDir . '/' . $filename),
                    'size_human' => $this->humanFileSize(filesize($videosDir . '/' . $filename)),
                ],
                'added_to_playlist' => !$alreadyExists,
            ]);
        }

        session()->flash('success', 'Video berhasil diupload.');
        return redirect()->route('settings.index');
    }

    /**
     * Delete video file dari folder public/videos.
     *
     * Safety: hanya hapus jika tidak ada di video_playlist (atau ada konfirmasi).
     * Default behavior: remove dari playlist dulu, baru delete file.
     */
    public function deleteVideo(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        $videosDir = public_path('videos');
        $filename  = basename($request->input('filename')); // anti path traversal
        $filePath  = $videosDir . DIRECTORY_SEPARATOR . $filename;

        if (!file_exists($filePath)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'File tidak ditemukan.'], 404);
            }
            session()->flash('error', 'File tidak ditemukan.');
            return redirect()->route('settings.index');
        }

        // Hapus dari playlist dulu
        $playlistJson = Setting::getValue('video_playlist', []);
        if (is_array($playlistJson)) {
            $filtered = array_values(array_filter($playlistJson, fn($e) => !isset($e['path']) || basename($e['path']) !== $filename));
            // Re-index order
            foreach ($filtered as $i => &$entry) {
                $entry['order'] = $i;
            }
            Setting::where('key', 'video_playlist')->update([
                'value'      => json_encode($filtered),
                'updated_at' => now(),
            ]);
        }

        // Delete file
        unlink($filePath);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Video berhasil dihapus.',
            ]);
        }

        session()->flash('success', 'Video berhasil dihapus.');
        return redirect()->route('settings.index');
    }

    /**
     * Format bytes jadi human-readable string (KB, MB, GB).
     */
    private function humanFileSize(int $bytes): string
    {
        if ($bytes < 1024) return $bytes . ' B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    /**
     * Public API: list available video files (untuk admin UI auto-detect).
     * Tidak butuh auth karena cuma return info file (nama, size), bukan data sensitif.
     */
    public function listVideosApi()
    {
        return response()->json($this->listVideos());
    }
}
