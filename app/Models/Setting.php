<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    public static function get(string $key, $default = null)
    {
        try {
            $setting = static::where('key', $key)->first();
            if (!$setting) {
                return $default;
            }

            if ($setting->type === 'json' || $setting->type === 'array') {
                return json_decode($setting->value, true) ?: $default;
            }
            if ($setting->type === 'boolean') {
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            }
            if ($setting->type === 'integer' || $setting->type === 'int') {
                return (int) $setting->value;
            }
            if ($setting->type === 'float' || $setting->type === 'decimal') {
                return (float) $setting->value;
            }

            return $setting->value ?? $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, $value, string $type = 'text', string $group = 'store'): self
    {
        $valToStore = $value;
        if (is_array($value) || is_object($value)) {
            $valToStore = json_encode($value);
            $type = 'json';
        } elseif (is_bool($value)) {
            $valToStore = $value ? '1' : '0';
            $type = 'boolean';
        }

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $valToStore,
                'type' => $type,
                'group' => $group,
            ]
        );
    }

    public static function getGroup(string $group): array
    {
        $settings = static::where('group', $group)->get();
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = static::get($setting->key);
        }
        return $result;
    }
}
