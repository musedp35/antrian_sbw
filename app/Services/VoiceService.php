<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class VoiceService
{
    /**
     * Text-to-Speech menggunakan Web Speech API (browser-side).
     * Pada sisi server ini menghasilkan teks yang akan diparsing oleh browser client.
     */
    public function generateTextForTTS(string $ticketNumber, string $type): string
    {
        $readableNumber = $this->readableNumber($ticketNumber);
        $typeLabel = match ($type) {
            'spp' => 'S P P',
            'tunai' => 'Tunai',
            'tabungan' => 'Tabungan',
            default => $type,
        };

        return "Nomor antrian {$readableNumber}, silakan menuju loket {$typeLabel}";
    }

    /**
     * Convert ticket number to readable Indonesian format.
     * e.g., "A-001" → "A satu nol nol satu"
     */
    protected function readableNumber(string $ticketNumber): string
    {
        // Extract prefix and number parts
        $parts = explode('-', $ticketNumber);
        if (count($parts) !== 2) {
            return $ticketNumber;
        }

        [$prefix, $number] = $parts;
        $readableNum = '';
        for ($i = 0; $i < strlen($number); $i++) {
            $digit = $number[$i];
            $readableNum .= match ((int)$digit) {
                0 => 'nol ',
                1 => 'satu ',
                2 => 'dua ',
                3 => 'tiga ',
                4 => 'empat ',
                5 => 'lima ',
                6 => 'enam ',
                7 => 'tujuh ',
                8 => 'delapan ',
                9 => 'sembilan ',
                default => $digit . ' ',
            };
        }

        return trim($prefix) . ' ' . trim($readableNum);
    }
}
