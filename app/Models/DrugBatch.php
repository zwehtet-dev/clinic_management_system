<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DrugBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'drug_id',
        'batch_number',
        'purchase_price',
        'sell_price',
        'quantity_received',
        'quantity_available',
        'expiry_date',
        'received_date'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'expiry_date' => 'date',
        'received_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($drugBatch) {
            if (empty($drugBatch->batch_number) && $drugBatch->drug_id) {
                $drugBatch->batch_number = $drugBatch->generateBatchNumber($drugBatch->drug_id);
            }
        });
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function invoiceItems()
    {
        return $this->morphMany(InvoiceItem::class, 'itemable');
    }

    public function getIsExpireAlertAttribute(): bool
    {
        if (! $this->expiry_date || ! $this->drug) {
            return false;
        }

        return $this->expiry_date->lte(
            Carbon::now()->addDays($this->drug->expire_alert ?? 0)
        );
    }


    public function reduceStock($quantity)
    {
        if ($this->quantity_available >= $quantity) {
            $this->quantity_available -= $quantity;
            $this->save();
            return true;
        }
        return false;
    }

    public static function generateBatchNumber($drugId)
    {
        $prefix = "BAT-{$drugId}-";

        // Get the latest batch for the given drug to determine next number
        $lastBatch = self::where('drug_id', $drugId)
            ->where('batch_number', 'like', $prefix . '%')
            ->orderBy('batch_number', 'desc')
            ->first();

        if ($lastBatch) {
            // Extract the number from the last batch number
            $lastNumber = (int) substr($lastBatch->batch_number, strrpos($lastBatch->batch_number, '-') + 1);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

}
