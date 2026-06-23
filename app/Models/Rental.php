<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rental extends Model
{
    protected $fillable = [
        'business_id',
        'customer_id',
        'room_id',
        'start_date',
        'end_date',
        'monthly_rent',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'monthly_rent' => 'decimal:2',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(ProductService::class, 'room_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function monthsElapsed(): int
    {
        $start = CarbonImmutable::parse($this->start_date);
        $end = $this->end_date ? CarbonImmutable::parse($this->end_date) : CarbonImmutable::today();
        return max(1, $start->diffInMonths($end) + 1);
    }

    public function totalExpected(): float
    {
        return round($this->monthsElapsed() * (float) $this->monthly_rent, 2);
    }

    public function totalPaid(): float
    {
        return (float) $this->invoices()->where('status', 'paid')->sum('total');
    }

    public function balance(): float
    {
        return round($this->totalExpected() - $this->totalPaid(), 2);
    }
}
