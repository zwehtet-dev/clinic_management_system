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
            'clinic_email' => ['value' => 'info@zweclinic.com', 'type' => 'email', 'description' => 'Clinic email address'],
            'clinic_website' => ['value' => 'https://zweclinic.com', 'type' => 'url', 'description' => 'Clinic website'],

            // Operational Settings
            'auto_generate_reports' => ['value' => '1', 'type' => 'boolean', 'description' => 'Automatically generate daily reports'],
            'auto_print_invoices' => ['value' => '0', 'type' => 'boolean', 'description' => 'Auto print invoices when created'],
            'enable_notifications' => ['value' => '1', 'type' => 'boolean', 'description' => 'Enable system notifications'],
            'backup_frequency' => ['value' => 'daily', 'type' => 'string', 'description' => 'Database backup frequency'],

            // Financial Settings
            'consultation_fee_default' => ['value' => '10000.00', 'type' => 'float', 'description' => 'Default consultation fee'],
            'invoice_terms' => ['value' => 'Payment due upon receipt', 'type' => 'text', 'description' => 'Default invoice terms'],
            'tax_rate' => ['value' => '0.00', 'type' => 'float', 'description' => 'Default tax rate percentage'],
            'currency_symbol' => ['value' => 'Ks', 'type' => 'string', 'description' => 'Currency symbol'],

            // Print Settings (Simple web-based printing)
            'auto_open_print_window' => ['value' => '1', 'type' => 'boolean', 'description' => 'Auto-open print window after invoice creation'],
            'print_logo' => ['value' => '1', 'type' => 'boolean', 'description' => 'Include clinic logo on receipts'],
            'print_format' => ['value' => 'receipt', 'type' => 'string', 'description' => 'Default print format (receipt, thermal, a4)'],
            'thermal_max_items' => ['value' => '12', 'type' => 'integer', 'description' => 'Maximum items to show in detail on thermal receipts'],
            'receipt_max_items' => ['value' => '15', 'type' => 'integer', 'description' => 'Maximum items to show in detail on standard receipts'],

            // Security Settings
            'session_timeout' => ['value' => '120', 'type' => 'integer', 'description' => 'Session timeout in minutes'],
            'require_password_change' => ['value' => '0', 'type' => 'boolean', 'description' => 'Require periodic password changes'],
            'enable_audit_log' => ['value' => '1', 'type' => 'boolean', 'description' => 'Enable audit logging'],
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

    /**
     * Get all settings as an array
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('all_settings', 3600, function () {
            $settings = self::where('is_active', true)->get();
            $result = [];

            foreach ($settings as $setting) {
                $result[$setting->key] = match ($setting->type) {
                    'boolean' => (bool) $setting->value,
                    'integer' => (int) $setting->value,
                    'float' => (float) $setting->value,
                    'json' => json_decode($setting->value, true),
                    default => $setting->value,
                };
            }

            return $result;
        });
    }

    /**
     * Get settings by category
     */
    public static function getByCategory(string $category): array
    {
        $allSettings = self::getAllSettings();
        $categorySettings = [];

        foreach ($allSettings as $key => $value) {
            if (str_starts_with($key, $category . '_')) {
                $categorySettings[$key] = $value;
            }
        }

        return $categorySettings;
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('all_settings');
        
        // Clear individual setting caches
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget("setting_{$setting->key}");
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
