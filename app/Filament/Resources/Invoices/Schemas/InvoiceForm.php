<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Drug;
use App\Models\Visit;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\DrugSale;
use App\Models\DrugBatch;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Information')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->unique(ignoreRecord: true)
                            ->default(function(){
                                return Invoice::generateInvoiceNumber();
                            })
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->required(),
                        Select::make('invoiceable_type')
                            ->label('Invoice Type')
                            ->options([
                                Visit::class => 'Visit Invoice',
                                DrugSale::class => 'Drug Sale Invoice',
                            ])
                            ->required()
                            ->reactive(),
                        Select::make('invoiceable_id')
                            ->label('Reference')
                            ->options(function (callable $get) {
                                if ($get('invoiceable_type') === Visit::class) {
                                    return Visit::pluck('public_id', 'public_id');
                                }
                                if ($get('invoiceable_type') === DrugSale::class) {
                                    return DrugSale::pluck('public_id', 'public_id');
                                }
                                return [];
                            })
                            ->required()
                            ->preload()
                            ->searchable()
                            ->afterStateUpdated(function(Set $set,Get $get,$state){
                                if($get('invoiceable_type')==Visit::class){
                                    $invoiceItems = $get('invoice_items') ?? [];
                                    $visit = Visit::fine($state);
                                    $service = Service::where('name', 'Consultation Fee');
                                    $invoiceItems[] = [
                                        'itemable_type' => Service::class,
                                        'itemable_id' => $service->id,
                                        'quantity' => 1,
                                        'unit_price' => $visit->consultation_fee,
                                        'line_total' => $visit->consultation_fee,
                                    ];

                                    $set('invoice_items', $invoiceItems);
                                }
                            }),
                        DatePicker::make('invoice_date')
                            ->default(now())
                            ->required(),
                        Select::make('status')
                            ->options(['paid' => 'Paid', 'cancelled' => 'Cancelled'])
                            ->default('paid')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Invoice Summary')
                    ->schema([
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->suffix(' Ks')
                            ->disabled()
                            ->dehydrated()
                            ->live()
                            ->afterStateHydrated(function (Set $set, Get $get) {
                                $items = $get('invoice.invoice_items') ?? [];
                                $total = collect($items)->sum('line_total');
                                $set('invoice.total_amount', $total);
                            }),

                        Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Additional notes for the invoice...'),
                    ])
                    ->columns(2),

                Section::make('Invoice Items')
                   ->schema([
                        Repeater::make('invoice_items')
                            ->label('Drugs')
                            ->schema([

                                TextInput::make('invoice_items.itemable_type')
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
                                            $set('invoice_items.itemable_id', null);
                                            return;
                                        }

                                        // Update batch select options to only include batches of selected drug
                                        $batches = DrugBatch::where('drug_id', $drugId)
                                            ->where('quantity_available', '>', 0)
                                            ->where('expiry_date', '>', now())
                                            ->orderBy('expiry_date')
                                            ->pluck('batch_number', 'id')
                                            ->toArray();

                                        $set('invoice_items.itemable_id_options', $batches); // store options dynamically
                                    }),

                                // Batch select
                                Select::make('invoice_items.itemable_id')
                                    ->label('Batch')
                                    ->options(function (Get $get) {
                                        return $get('invoice_items.itemable_id_options') ?? [];
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


                                TextInput::make('invoice_items.quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $unitPrice = floatval($get('unit_price') ?? 0);
                                        $quantity = floatval($state ?? 0);
                                        $set('line_total', $unitPrice * $quantity);
                                    }),



                                TextInput::make('invoice_items.unit_price')
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

                                TextInput::make('invoice_items.line_total')
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
                            ->columnSpanFull(),

                        // Services as checkboxes
                        CheckboxList::make('selected_services')
                            ->label('Select Services')
                            ->options(function () {
                                return Service::where('is_active', true)->pluck('name', 'id');
                            })
                            ->columns(3)
                            ->visible(fn (Get $get) => $get('invoiceable_type') === Visit::class)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                // Dynamically add service items to invoice_items
                                $invoiceItems = $get('invoice_items') ?? [];
                                foreach ($state as $serviceId) {
                                    $service = Service::find($serviceId);
                                    if ($service && !collect($invoiceItems)->contains('itemable_id', $service->id)) {
                                        $invoiceItems[] = [
                                            'itemable_type' => Service::class,
                                            'itemable_id' => $service->id,
                                            'quantity' => 1,
                                            'unit_price' => $service->price,
                                            'line_total' => $service->price,
                                        ];
                                    }
                                }
                                $set('invoice_items', $invoiceItems);
                            }),
                    ])
                    ->columnSpanFull(),



            ]);
    }
}
