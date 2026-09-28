<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'status',
        'message',
        'details',
        'items_processed',
        'items_success',
        'items_failed',
    ];

    protected $casts = [
        'details' => 'array',
        'items_processed' => 'integer',
        'items_success' => 'integer',
        'items_failed' => 'integer',
    ];

    public static function log(string $type, string $status, string $message, ?array $details = null, int $processed = 0, int $success = 0, int $failed = 0): self
    {
        return static::create([
            'type' => $type,
            'status' => $status,
            'message' => $message,
            'details' => $details,
            'items_processed' => $processed,
            'items_success' => $success,
            'items_failed' => $failed,
        ]);
    }
}
