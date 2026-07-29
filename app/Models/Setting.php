<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Get setting value by key (with type-aware casting).
     *
     * Supported types:
     * - 'boolean' → bool
     * - 'number'  → int
     * - 'json'    → array (auto-decoded)
     * - 'string'  → string (default)
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) return $default;

        return match ($setting->type) {
            'number'  => (int) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($setting->value ?? '[]', true) ?? [],
            default   => $setting->value,
        };
    }

    /**
     * Set setting value by key (auto-detect type).
     */
    public static function setValue(string $key, mixed $value): void
    {
        $setting = self::firstOrCreate(
            ['key' => $key],
            [
                'value' => is_bool($value) ? json_encode($value) : (string) $value,
                'type'  => is_bool($value) ? 'boolean' : 'string',
            ]
        );
        $setting->update(['value' => is_bool($value) ? json_encode($value) : (string) $value]);
    }

    /**
     * Set multiple settings at once.
     */
    public static function setValues(array $data): void
    {
        foreach ($data as $key => $value) {
            self::setValue($key, $value);
        }
    }
}
