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
                            ->default(function(){
                                if(request()->exists('visit_id')){
                                    return Visit::class;
                                }
                                if(request()->exists('drug_sale_id')){
                                    return DrugSale::class;
                                }
                                return Visit::class; // Default to visit
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                // Clear all data when type changes
                                $set('invoiceable_id', null);
                                $set('drug_items', []);
                                $set('selected_services', []);
                                $set('total_amount', 0);
                            }),

                        Select::make('invoiceable_id')
                            ->label('Reference')
                            ->options(function (Get $get) {
                                $type = $get('invoiceable_type');
                                if ($type === Visit::class) {
                                    return Visit::with('patient', 'doctor')
                                        ->latest()
                                        ->limit(100)
                                        ->get()
                                        ->mapWithKeys(function ($visit) {
                                            return [$visit->id => "{$visit->public_id} - {$visit->patient->name}"];
                                        })
                                        ->toArray();
                                }
                                if ($type === DrugSale::class) {
                                    return DrugSale::with('patient')
                                        ->latest()
                                        ->limit(100)
                                        ->get()
                                        ->mapWithKeys(function ($drugSale) {
                                            $customer = $drugSale->patient ? $drugSale->patient->name : $drugSale->buyer_name;
                                            return [$drugSale->id => "{$drugSale->public_id} - {$customer}"];
                                        })
                                        ->toArray();
                                }
                                return [];
                            })
                            ->getOptionLabelUsing(function ($value, Get $get) {
                                $type = $get('invoiceable_type');
                                if ($type === Visit::class) {
                                    $visit = Visit::with('patient', 'doctor')->find($value);
                                    if ($visit) {
                                        return "{$visit->public_id} - {$visit->patient->name}";
                                    }
                                } elseif ($type === DrugSale::class) {
                                    $drugSale = DrugSale::with('patient')->find($value);
                                    if ($drugSale) {
                                        $customer = $drugSale->patient ? $drugSale->patient->name : $drugSale->buyer_name;
                                        return "{$drugSale->public_id} - {$customer}";
                                    }
                                }
                                return "Unknown Reference";
                            })
                            ->default(function(){
                                if(request()->exists('visit_id')){
                                    return request()->get('visit_id');
                                }
                                if(request()->exists('drug_sale_id')){
                                    return request()->get('drug_sale_id');
                                }
                                return null;
                            })
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function(Set $set, Get $get, $state){
                                if(!$state) return;

                                $type = $get('invoiceable_type');
                                if($type === Visit::class){
                                    $visit = Visit::find($state);
                                    if($visit) {
                                        // Auto-set consultation fee in total
                                        $set('total_amount', $visit->consultation_fee);
                                    }
                                } elseif($type === DrugSale::class) {
                                    $drugSale = DrugSale::find($state);
                                    if($drugSale) {
                                        // Auto-populate drug items from drug sale
                                        // $drugItems = [];
                                        // foreach($drugSale->drugSaleItems as $saleItem) {
                                        //     $drugItems[] = [
                                        //         'itemable_type' => DrugBatch::class,
                                        //         'itemable_id' => $saleItem->batch_id ?? $saleItem->drug_id,
                                        //         'quantity' => $saleItem->quantity,
                                        //         'unit_price' => $saleItem->unit_price,
                                        //         'line_total' => $saleItem->line_total,
                                        //     ];
                                        // }

                                        // $set('drug_items', $drugItems);
                                        // $set('total_amount', $drugSale->total_amount);
                                    }
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

                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->suffix(' Ks')
                            ->disabled()
                            ->default(function(){
                                if(request()->exists('visit_id'))
                                {
                                    $visit = Visit::find(request()->get('visit_id'));
                                    if($visit) {
                                        return $visit->consultation_fee;
                                    }

                                }
                                return 0;
                            })
                            ->dehydrated(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                // Drug Items Section (Visible for all types)
                Section::make('Drugs/Medicines')
                    ->schema([
                        Repeater::make('drug_items')
                            ->label('Drug Items')
                            ->schema([

                                // Combined search for drugs by name or batch number
                                Select::make('itemable_search')
                                    ->label('Search Drug/Batch')
                                    ->searchable()
                                    ->preload(false)
                                    ->getSearchResultsUsing(function (string $search) {
                                        // Search drug batches by batch number, drug name, or generic name
                                        return DrugBatch::with('drug')
                                            ->where('quantity_available', '>', 0)
                                            ->where('expiry_date', '>', now())
                                            ->where(function ($query) use ($search) {
                                                $query->where('batch_number', 'like', "%{$search}%")
                                                    ->orWhereHas('drug', function ($drugQuery) use ($search) {
                                                        $drugQuery->where('name', 'like', "%{$search}%")
                                                            ->orWhere('generic_name', 'like', "%{$search}%");
                                                    });
                                            })
                                            ->limit(50)
                                            ->orderBy('expiry_date')
                                            ->get()
                                            ->mapWithKeys(function ($batch) {
                                                return [
                                                    $batch->id => "#{$batch->batch_number} - {$batch->drug->name} ({$batch->drug->drugForm->name}) (Stock: {$batch->quantity_available}) - Exp: {$batch->expiry_date->format('M d, Y')}"
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value) {
                                        $batch = DrugBatch::with('drug')->find($value);
                                        return $batch ? "#{$batch->batch_number} - {$batch->drug->name} ({$batch->drug->drugForm->name}) (Stock: {$batch->quantity_available}) - Exp: {$batch->expiry_date->format('M d, Y')}" : null;
                                    })
                                    ->required()
                                    ->live()
                                    ->dehydrated(false)
                                    ->columnSpanFull()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        if (!$state) {
                                            $set('itemable_id', null);
                                            $set('batch_number', null);
                                            $set('unit_price', 0);
                                            $set('line_total', 0);
                                            return;
                                        }

                                        $batch = DrugBatch::find($state);
                                        if ($batch) {
                                            $set('itemable_id', $batch->id ?? null);
                                            $set('batch_number', $batch->batch_number ?? null);
                                            $set('unit_price', $batch->sell_price ?? $batch->drug->price);
                                            $quantity = floatval($get('quantity') ?: 1);
                                            $set('line_total', ($batch->sell_price ?? $batch->drug->price) * $quantity);
                                        }

                                        // Update invoice total
                                        self::updateInvoiceTotal($set, $get);
                                    }),

                                // Hidden fields for proper storage
                                TextInput::make('itemable_id')
                                    ->hidden()
                                    ->dehydrated(),

                                TextInput::make('batch_number')
                                    // ->hidden()
                                    ->label('Batch Number')
                                    ->live()
                                    ->default(null)
                                    ->dehydrated(),


                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $unitPrice = floatval($get('unit_price') ?? 0);
                                        $quantity = floatval($state ?? 1);
                                        $set('line_total', $unitPrice * $quantity);

                                        // Update invoice total
                                        self::updateInvoiceTotal($set, $get);
                                    })
                                    ->maxValue(function(Get $get) {
                                        if ($get('itemable_id')) {
                                            $batch = DrugBatch::find($get('itemable_id'));
                                            return $batch ? $batch->quantity_available : 999;
                                        }
                                        return 999;
                                    })
                                    ->helperText(function(Get $get) {
                                        if ($get('itemable_id')) {
                                            $batch = DrugBatch::find($get('itemable_id'));
                                            if ($batch) {
                                                $expiryDays = (int) $batch->expiry_date->diffInDays();
                                                $stockInfo = "Available: {$batch->quantity_available}";
                                                $expiryInfo = $expiryDays <= 30 ? " ⚠️ Expires in {$expiryDays} days" : "";
                                                return $stockInfo . $expiryInfo;
                                            }
                                        }
                                        return '';
                                    }),

                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->suffix('Ks')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $unitPrice = floatval($state ?? 0);
                                        $quantity = floatval($get('quantity') ?? 1);
                                        $set('line_total', $unitPrice * $quantity);

                                        // Update invoice total
                                        self::updateInvoiceTotal($set, $get);
                                    }),

                                TextInput::make('line_total')
                                    ->label('Total')
                                    ->numeric()
                                    ->suffix('Ks')
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->columns(4)
                            ->reorderable(false)
                            ->cloneable()
                            ->addActionLabel('Add Drug')
                            ->columnSpanFull()
                            ->live()
                            ->dehydrated()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::updateInvoiceTotal($set, $get);
                            }),
                    ])
                    ->columnSpanFull(),

                // Services Section (Only visible for Visit invoices)
                Section::make('Medical Services')
                    ->schema([
                        CheckboxList::make('selected_services')
                            ->label('Select Services')
                            ->options(function () {
                                return Service::where('is_active', true)
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn ($service) => [
                                        $service->id => "{$service->name} - {$service->price} Ks"
                                    ])
                                    ->toArray();
                            })
                            ->columns(3)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                self::updateInvoiceTotal($set, $get);
                            }),
                    ])
                    ->visible(fn (Get $get) => $get('invoiceable_type') === Visit::class)
                    ->columnSpanFull(),

                Section::make('Invoice Summary')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(4)
                            ->placeholder('Additional notes for the invoice...')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Helper method to update invoice total
     */
    private static function updateInvoiceTotal(Set $set, Get $get): void
    {
        // Get consultation fee if it's a visit invoice
        $consultationFee = 0;
        $type = $get('invoiceable_type');
        if ($type === Visit::class && $get('invoiceable_id')) {
            $visit = Visit::find($get('invoiceable_id'));
            if ($visit) {
                $consultationFee = $visit->consultation_fee;
            }
        }

        // Calculate drug items total
        $drugItems = $get('drug_items') ?? [];
        $drugTotal = collect($drugItems)->sum('line_total');

        // Calculate services total (only for visit invoices)
        $servicesTotal = 0;
        if ($type === Visit::class) {
            $selectedServices = $get('selected_services') ?? [];
            foreach ($selectedServices as $serviceId) {
                $service = Service::find($serviceId);
                if ($service) {
                    $servicesTotal += $service->price;
                }
            }
        }

        $totalAmount = $consultationFee + $drugTotal + $servicesTotal;
        $set('total_amount', $totalAmount);
    }
}
