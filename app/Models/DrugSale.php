<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'patient_id',
        'buyer_name',
        'sale_date',
        'total_amount'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function invoice()
    {
        return $this->morphOne(Invoice::class, 'invoiceable');
    }

    public static function generatePublicId(): string
    {
        $year = now()->year;
        $lastSale = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastSale ?
            (int) substr($lastSale->public_id, -6) + 1 : 1;

        return "DS-{$year}-" . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('sale_date', now()->toDateString());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('sale_date', now()->month)
                    ->whereYear('sale_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('sale_date', now()->year);
    }


    public function getBuyerDisplayNameAttribute(): string
    {
        return $this->patient ? $this->patient->name : ($this->buyer_name ?: 'Walk-in Customer');
    }
}
