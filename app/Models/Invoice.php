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

    public function invoiceable()
    {
        return $this->morphTo();
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
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

}
