<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'general' => ['title' => 'Konfigurasi Umum', 'settings' => []],
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
            $groups[$s->group]['settings'][] = [
                'key'       => $s->key,
                'value'     => is_bool($val) ? ($val ? 'true' : 'false') : $val,
                'type'      => $s->type,
                'desc'      => $s->description,
            ];
        }

        return view('settings.index', compact('groups'));
    }

    /**
     * Cast setting value based on type.
     */
    private function castValue(?string $value, string $type): mixed
    {
        return match ($type) {
            'number'  => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default   => $value,
        };
    }

    /**
     * Update settings via bulk form submission.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings.*.key'   => 'required|string',
            'settings.*.value' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // PERFORMANCE FIX: Load all relevant settings once
            $keys = array_column($validated['settings'], 'key');
            $existingSettings = Setting::whereIn('key', $keys)->get()->keyBy('key');

            foreach ($validated['settings'] as $item) {
                // Only update keys that exist in database
                $setting = $existingSettings[$item['key']] ?? null;
                if (!$setting) continue;

                // Skip empty values for non-boolean types
                $currentVal = $this->castValue($setting->value, $setting->type);
                if (empty($item['value']) && !is_bool($currentVal)) {
                    continue;
                }

                $setting->update(['value' => $item['value']]);
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
     */
    public function apiIndex()
    {
        $settings = Setting::pluck('value', 'key');
        return response()->json($settings);
    }
}
