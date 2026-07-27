<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            // TTS Settings
            'tts_volume' => [
                'value' => '100',
                'type'  => 'number',
                'group' => 'tts',
                'desc'  => 'Volume suara panggilan (0-100)',
            ],
            'tts_rate' => [
                'value' => '0.9',
                'type'  => 'string',
                'group' => 'tts',
                'desc'  => 'Kecepatan suara panggilan (0.5 - 2.0)',
            ],
            'tts_auto_play' => [
                'value' => 'true',
                'type'  => 'boolean',
                'group' => 'tts',
                'desc'  => 'Auto-play suara saat tiket dipanggil',
            ],

            // Display Settings
            'display_refresh_rate' => [
                'value' => '2000',
                'type'  => 'number',
                'group' => 'display',
                'desc'  => 'Interval refresh display (ms)',
            ],
            'display_show_countdown' => [
                'value' => 'false',
                'type'  => 'boolean',
                'group' => 'display',
                'desc'  => 'Tampilkan countdown timer di display',
            ],

            // General
            'app_name' => [
                'value' => 'Sistem Antrian SBW',
                'type'  => 'string',
                'group' => 'general',
                'desc'  => 'Nama aplikasi',
            ],
            'queue_prefix_spp' => [
                'value' => 'A',
                'type'  => 'string',
                'group' => 'general',
                'desc'  => 'Prefix antrian SPP',
            ],
            'queue_prefix_tunai' => [
                'value' => 'B',
                'type'  => 'string',
                'group' => 'general',
                'desc'  => 'Prefix antrian Tunai',
            ],
            'queue_prefix_tabungan' => [
                'value' => 'C',
                'type'  => 'string',
                'group' => 'general',
                'desc'  => 'Prefix antrian Tabungan',
            ],
        ];

        foreach ($defaults as $key => $data) {
            Setting::firstOrCreate(
                ['key' => $key],
                [
                    'value'       => $data['value'],
                    'type'        => $data['type'],
                    'group'       => $data['group'],
                    'description' => $data['desc'],
                ]
            );
        }

        $this->command->info('Settings seeded successfully.');
    }
}
