<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Drug extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'name',
        'catelog',
        'generic_name',
        'drug_form_id',
        'strength',
        'unit',
        'min_stock',
        'expire_alert',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expire_alert' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($drug) {
            if (empty($drug->public_id)) {
                $drug->public_id = self::generatePublicId();
            }
        });
    }

    public function batches()
    {
        return $this->hasMany(DrugBatch::class);
    }

    public function availableBatches()
    {
        return $this->batches()->where('quantity_available', '>', 0)
                              ->where('expiry_date', '>', now())
                              ->orderBy('expiry_date', 'asc');
    }

    public function getTotalStockAttribute()
    {
        return $this->availableBatches()->sum('quantity_available');
    }

    public function getIsLowStockAttribute()
    {
        return $this->total_stock <= $this->min_stock;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function drugForm()
    {
        return $this->belongsTo(DrugForm::class);
    }

    public static function generatePublicId(): string
    {
        $year = now()->format('Y');
        $prefix = "DRU-{$year}-";

        // Get the latest drug ID
        $lastDrug = self::latest('id')->first();

        $nextNumber = $lastDrug ? $lastDrug->id + 1 : 1;

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
