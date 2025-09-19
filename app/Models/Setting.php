<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->where('is_active', true)->first();

            if (!$setting) {
                return $default;
            }

            return match ($setting->type) {
                'boolean' => (bool) $setting->value,
                'integer' => (int) $setting->value,
                'float' => (float) $setting->value,
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    public static function set(string $key, $value, string $type = 'string', string $description = null): void
    {
        $formattedValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };

        self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $formattedValue,
                'type' => $type,
                'description' => $description,
                'is_active' => true,
            ]
        );

        // Clear cache
        Cache::forget("setting_{$key}");
    }

    public static function initializeDefaults(): void
    {
        $defaults = [
            // App Information
            'app_name' => ['value' => 'Zwe Clinic Management', 'type' => 'string', 'description' => 'Application name'],
            'clinic_name' => ['value' => 'Zwe Clinic', 'type' => 'string', 'description' => 'Clinic name for documents and printing'],
            'clinic_address' => ['value' => '123 Main Street, City, State 12345', 'type' => 'text', 'description' => 'Clinic address'],
            'clinic_phone' => ['value' => '+1 (555) 123-4567', 'type' => 'string', 'description' => 'Clinic phone number'],

            // Operational Settings
            'auto_generate_reports' => ['value' => '1', 'type' => 'boolean', 'description' => 'Automatically generate daily reports'],
            'auto_print_invoices' => ['value' => '0', 'type' => 'boolean', 'description' => 'Auto print invoices when created'],

            // Financial Settings
            'consultation_fee_default' => ['value' => '10000.00', 'type' => 'float', 'description' => 'Default consultation fee'],
            'invoice_terms' => ['value' => 'Payment due upon receipt', 'type' => 'text', 'description' => 'Default invoice terms'],
        ];

        foreach ($defaults as $key => $data) {
            self::firstOrCreate(
                ['key' => $key],
                [
                    'value' => $data['value'],
                    'type' => $data['type'],
                    'description' => $data['description'],
                    'is_active' => true,
                ]
            );
        }
    }


    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue($key, $value, $type = 'string', $description = null, $is_active = true)
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description,
                'is_active' => $is_active
            ]
        );
    }


}
