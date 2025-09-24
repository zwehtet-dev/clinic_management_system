<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'invoiceable_id',
        'invoiceable_type',
        'invoice_date',
        'total_amount',
        'notes',
        'status',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function invoiceable()
    {
        return $this->morphTo();
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function drugItems()
    {
        return $this->invoiceItems()->whereHasMorph('itemable', [\App\Models\DrugBatch::class]);
    }

    public function serviceItems()
    {
        return $this->invoiceItems()->whereHasMorph('itemable', [\App\Models\Service::class]);
    }

    public static function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "INV-{$year}-";

        // Get the latest invoice ID
        $lastInvoice = self::latest('id')->first();

        $nextNumber = $lastInvoice ? $lastInvoice->id + 1 : 1;

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    protected static function booted()
    {
        static::deleting(function ($invoice) {
            // Restore stock for drug items when invoice is deleted
            foreach ($invoice->drugItems as $item) {
                if ($item->itemable_type === DrugBatch::class) {
                    $batch = DrugBatch::find($item->itemable_id);
                    if ($batch) {
                        $batch->increment('quantity_available', $item->quantity);
                    }
                }
            }
        });
    }

}
