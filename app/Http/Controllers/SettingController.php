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

        $settings = Setting::orderBy('group')->orderBy('key')->get();
        foreach ($settings as $s) {
            $val = Setting::getValue($s->key, null);
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
            foreach ($validated['settings'] as $item) {
                // Only update keys that exist in database
                $setting = Setting::where('key', $item['key'])->first();
                if (!$setting) continue;

                // Skip empty values for non-boolean types
                if (empty($item['value']) && !is_bool(Setting::getValue($item['key'], null))) {
                    continue;
                }

                $setting->update(['value' => $item['value']]);
            }
            DB::commit();

            session()->flash('success', 'Pengaturan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }

        return redirect()->route('settings.index');
    }
}
