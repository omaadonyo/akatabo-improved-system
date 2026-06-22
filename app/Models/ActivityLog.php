<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'properties',
        'business_id',
        'subject_type',
        'subject_id',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('action', $type);
    }

    public static function log(
        string $action,
        string $description,
        ?array $properties = null,
        ?int $businessId = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
    ): self {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'properties' => $properties,
            'business_id' => $businessId ?? activeBusinessId(),
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
        ]);
    }
}
