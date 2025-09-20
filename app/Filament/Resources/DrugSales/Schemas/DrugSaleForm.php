<?php

namespace App\Filament\Resources\DrugSales\Schemas;

use App\Models\Drug;
use App\Models\Invoice;
use App\Models\DrugSale;
use App\Models\DrugBatch;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class DrugSaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sale Information')
                    ->schema([
                        TextInput::make('public_id')
                            ->label('Sale ID')
                            ->default(fn () => DrugSale::generatePublicId())
                            ->required(),
                        Select::make('patient_id')
                            ->label('Patient (Optional)')
                            ->relationship('patient', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('age')->numeric()->required(),
                                Select::make('gender')
                                    ->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'])
                                    ->required(),
                                TextInput::make('phone'),
                                Textarea::make('address'),
                            ])
                            ->live(),
                        TextInput::make('buyer_name')
                            ->label('Customer Name')
                            ->visible(fn (Get $get): bool => !$get('patient_id'))
                            ->required(fn (Get $get): bool => !$get('patient_id')),
                        DatePicker::make('sale_date')
                            ->default(now())
                            ->required(),
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->default(0)
                            ->suffix('Ks')
                            ->disabled()
                            ->dehydrated() // keep in DB
                            ->live()
                            ->afterStateHydrated(function (Set $set, Get $get) {
                                $items = $get('invoice.invoice_items') ?? [];
                                $total = collect($items)->sum('line_total');
                                $set('invoice.total_amount', $total);
                            }),

                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Section::make('Invoice & Services')
                    ->schema([
                        Toggle::make('create_invoice')
                            ->label('Create Invoice for this visit')
                            ->default(true)
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('invoice.invoice_number')
                            ->label('Invoice Number')
                            ->unique(ignoreRecord: true)
                            ->default(function(){
                                return Invoice::generateInvoiceNumber();
                            })
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->required()
                            ->visible(fn (Get $get): bool => $get('create_invoice') === true),

                        DatePicker::make('invoice.invoice_date')
                            ->default(now())
                            ->required()
                            ->visible(fn (Get $get): bool => $get('create_invoice') === true),

                        Repeater::make('invoice.invoice_items')
                            ->label('Drugs')
                            ->schema([

                                TextInput::make('invoice.invoice_items.itemable_type')
                                    ->default(DrugBatch::class)
                                    ->visible(false),

                                // Drug search (virtual)
                                Select::make('drug_search')
                                    ->label('Drug')
                                    ->searchable()
                                    ->preload(false)
                                    ->getSearchResultsUsing(function (string $search) {
                                        return Drug::active()
                                            ->where('name', 'like', "%{$search}%")
                                            ->orWhere('generic_name', 'like', "%{$search}%")
                                            ->limit(50)
                                            ->orderBy('name', 'asc')
                                            ->get()
                                            ->mapWithKeys(fn ($drug) => [$drug->id => "{$drug->name} - {$drug->generic_name}"])
                                            ->toArray();
                                    })
                                    ->afterStateUpdated(function (Set $set, Get $get, $drugId) {
                                        if (!$drugId) {
                                            $set('invoice.invoice_items.itemable_id', null);
                                            return;
                                        }

                                        // Update batch select options to only include batches of selected drug
                                        $batches = DrugBatch::where('drug_id', $drugId)
                                            ->where('quantity_available', '>', 0)
                                            ->where('expiry_date', '>', now())
                                            ->orderBy('expiry_date')
                                            ->pluck('batch_number', 'id')
                                            ->toArray();

                                        $set('invoice.invoice_items.itemable_id_options', $batches); // store options dynamically
                                    }),

                                // Batch select
                                Select::make('invoice.invoice_items.itemable_id')
                                    ->label('Batch')
                                    ->options(function (Get $get) {
                                        return $get('invoice.invoice_items.itemable_id_options') ?? DrugBatch::where('expiry_date','>',now())->where('quantity_available','>',0)->get();
                                    })
                                    ->required()
                                    ->searchable()
                                    ->afterStateUpdated(function (Set $set, Get $get, $batchId) {
                                        if (!$batchId) return;

                                        $batch = DrugBatch::find($batchId);
                                        if ($batch) {
                                            // Update unit price
                                            $set('unit_price', $batch->sell_price);
                                            // Update drug_search to match selected batch
                                            $drug = $batch->drug;
                                            $set('drug_search', $drug->id);
                                        }
                                    }),


                                TextInput::make('invoice.invoice_items.quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $unitPrice = floatval($get('unit_price') ?? 0);
                                        $quantity = floatval($state ?? 0);
                                        $set('line_total', $unitPrice * $quantity);
                                    })
                                    ->maxValue(function(Get $get){
                                        $batchId = $get('invoice.invoice_items.itemable_id');
                                        if ($batchId) {
                                            $batch = DrugBatch::find($batchId);
                                            return $batch ? $batch->quantity_available : 1;
                                        }
                                        return 1;
                                    })
                                    ->helperText(function(Get $get){
                                        $batchId = $get('invoice.invoice_items.itemable_id');
                                        if ($batchId) {
                                            $batch = DrugBatch::find($batchId);
                                            return $batch ? "Available stock: {$batch->quantity_available}" : '';
                                        }
                                        return '';
                                    })
                                    ->helperText(function(Get $get){
                                        $batch = DrugBatch::find($get('invoice.invoice_items.itemable_id'));
                                        return "Available stock: {$batch->quantity_available}";
                                    }),




                                TextInput::make('invoice.invoice_items.unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->default(function (Get $get) {
                                        $itemId = $get('item_id');
                                        $drug = DrugBatch::find($itemId);
                                        return $drug ? $drug->sell_price : 0;
                                    })
                                    ->suffix('Ks')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $unitPrice = floatval($state ?? 0);
                                        $quantity = floatval($get('quantity') ?? 0);
                                        $set('line_total', $unitPrice * $quantity);
                                    }),

                                TextInput::make('invoice.invoice_items.line_total')
                                    ->label('Total')
                                    ->numeric()
                                    ->suffix('Ks')
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(5)
                            ->reorderable(false)
                            ->cloneable()
                            ->addActionLabel('Add Item')
                            ->visible(fn (Get $get): bool => $get('create_invoice') === true)
                            ->columnSpanFull(),


                        TextInput::make('invoice.total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->default(0)
                            ->suffix(' Ks')
                            ->disabled()
                            ->dehydrated()
                            ->visible(false)
                            ->live()
                            ->afterStateHydrated(function (Set $set, Get $get) {
                                $items = $get('invoice.invoice_items') ?? [];
                                $total = collect($items)->sum('line_total');
                                $set('invoice.total_amount', $total);
                            }),

                        Textarea::make('invoice.invoice_notes')
                            ->label('Invoice Notes')
                            ->rows(3)
                            ->placeholder('Additional notes for the invoice...')
                            ->visible(fn (Get $get): bool => $get('create_invoice') === true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),

            ]);
    }
}
