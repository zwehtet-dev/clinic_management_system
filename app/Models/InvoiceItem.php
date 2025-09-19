<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    //  Schema::create('invoice_items', function (Blueprint $table) {
    //         $table->id();
    //         $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
    //         $table->morphs('itemable'); // itemable_id + itemable_type
    //         $table->integer('quantity')->default(1);
    //         $table->decimal('unit_price', 12, 2);
    //         $table->decimal('line_total', 12, 2);
    //         $table->timestamps();

    //         $table->timestamps();

    protected $fillable = [
        'invoice_id',
        'itemable_id',
        'itemable_type',
        'quantity',
        'unit_price',
        'line_total',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function itemable()
    {
        return $this->morphTo();
    }
}
