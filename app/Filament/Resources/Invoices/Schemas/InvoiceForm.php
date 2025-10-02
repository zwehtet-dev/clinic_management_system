<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Drug;
use App\Models\Visit;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\DrugSale;
use App\Models\DrugBatch;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

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
                                return Visit::class;
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('invoiceable_id', null);
                                $set('drug_items', []);
                                $set('selected_services', []);
                                $set('consultation_fee', 0);
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
                                    $visit = Visit::with('doctor')->find($state);
                                    if($visit) {
                                        // Set consultation fee from visit
                                        $set('consultation_fee', $visit->consultation_fee ?? 0);
                                        // Auto-add consultation service for visit invoices
                                        self::updateConsultationService($set, $get, floatval($visit->consultation_fee ?? 0));
                                        self::updateInvoiceTotal($set, $get);
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
                            ->default(0)
                            ->dehydrated(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Drugs/Medicines')
                    ->schema([
                        Repeater::make('drug_items')
                            ->label('Drug Items')
                            ->schema([
                                Select::make('itemable_search')
                                    ->label('Search Drug/Batch')
                                    ->searchable()
                                    ->preload(false)
                                    ->getSearchResultsUsing(function (string $search) {
                                        return DrugBatch::with('drug.drugForm')
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
                                                $formName = $batch->drug->drugForm->name ?? 'N/A';
                                                return [
                                                    $batch->id => "#{$batch->batch_number} - {$batch->drug->public_id} - {$batch->drug->name} - {$batch->drug->catelog} - {$batch->drug->generic_name} ({$formName}) (Stock: {$batch->quantity_available}) - Exp: {$batch->expiry_date->format('M d, Y')}"
                                                ];
                                            })
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value) {
                                        $batch = DrugBatch::with('drug.drugForm')->find($value);
                                        if (!$batch) return null;
                                        $formName = $batch->drug->drugForm->name ?? 'N/A';
                                        return "#{$batch->batch_number} - {$batch->drug->public_id} - {$batch->drug->name} - {$batch->drug->catelog} - {$batch->drug->generic_name} ({$formName}) (Stock: {$batch->quantity_available}) - Exp: {$batch->expiry_date->format('M d, Y')}";
                                    })
                                    ->required()
                                    ->live(onBlur: true)
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

                                        $batch = DrugBatch::with('drug')->find($state);
                                        if ($batch) {
                                            $set('itemable_id', $batch->id);
                                            $set('batch_number', $batch->batch_number);

                                            $unitPrice = floatval($batch->sell_price ?? $batch->drug->price ?? 0);
                                            $set('unit_price', $unitPrice);

                                            $quantity = floatval($get('quantity') ?: 1);
                                            $lineTotal = round($unitPrice * $quantity, 2);
                                            $set('line_total', $lineTotal);

                                            // Delay total update to ensure all fields are set
                                            self::updateInvoiceTotal($set, $get);
                                        }
                                    }),

                                TextInput::make('itemable_id')
                                    ->hidden()
                                    ->dehydrated()
                                    ->required(),

                                TextInput::make('batch_number')
                                    ->label('Batch Number')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('quantity')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $unitPrice = floatval($get('unit_price') ?? 0);
                                        $quantity = floatval($state ?? 0);

                                        if ($quantity <= 0) {
                                            $set('quantity', 1);
                                            $quantity = 1;
                                        }

                                        $lineTotal = round($unitPrice * $quantity, 2);
                                        $set('line_total', $lineTotal);

                                        self::updateInvoiceTotal($set, $get);
                                    })
                                    ->maxValue(function(Get $get) {
                                        if ($get('itemable_id')) {
                                            $batch = DrugBatch::find($get('itemable_id'));
                                            return $batch ? $batch->quantity_available : 9999;
                                        }
                                        return 9999;
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
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        $unitPrice = floatval($state ?? 0);
                                        $quantity = floatval($get('quantity') ?? 1);

                                        if ($unitPrice < 0) {
                                            $set('unit_price', 0);
                                            $unitPrice = 0;
                                        }

                                        $lineTotal = round($unitPrice * $quantity, 2);
                                        $set('line_total', $lineTotal);

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
                            ->live(onBlur: true)
                            ->dehydrated()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                self::updateInvoiceTotal($set, $get);
                            })
                            ->deleteAction(
                                fn ($action) => $action->after(fn (Set $set, Get $get) => self::updateInvoiceTotal($set, $get))
                            ),
                    ])
                    ->columnSpanFull(),

                Section::make('Medical Services')
                    ->description('Edit consultation fee below. Consultation service will be auto-selected. You can also select additional services.')
                    ->schema([
                        TextInput::make('consultation_fee')
                            ->label('Consultation Fee')
                            ->numeric()
                            ->suffix(' Ks')
                            ->default(function (Get $get) {
                                if ($get('invoiceable_type') === Visit::class && $get('invoiceable_id')) {
                                    $visit = Visit::find($get('invoiceable_id'));
                                    return $visit ? $visit->consultation_fee : 0;
                                }
                                return 0;
                            })
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->dehydrated(false) // Don't save this field directly, it's handled via service items
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                // Update consultation service with new fee
                                self::updateConsultationService($set, $get, floatval($state ?? 0));
                                self::updateInvoiceTotal($set, $get);
                            })
                            ->helperText('Edit the consultation fee if needed. This will be added as a service item.')
                            ->columnSpan(1),

                        CheckboxList::make('selected_services')
                            ->label('All Medical Services')
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
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                self::updateInvoiceTotal($set, $get);
                            })
                            ->helperText('Consultation service is automatically managed based on the consultation fee above.')
                            ->columnSpan(2),
                    ])
                    ->columns(3)
                    ->visible(fn (Get $get) => $get('invoiceable_type') === Visit::class)
                    ->columnSpanFull(),

                Section::make('Invoice Summary')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(4)
                            ->placeholder('Additional notes for the invoice...')
                            ->columnSpanFull(),
                        Action::make('Recalculate Total')
                            ->label('Recalculate Total')
                            ->color('primary')
                            ->action(function (Set $set, Get $get) {
                                InvoiceForm::updateInvoiceTotal($set, $get);
                            })
                            ->icon(Heroicon::OutlinedCalculator),

                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Update consultation service with dynamic fee
     */
    private static function updateConsultationService(Set $set, Get $get, float $consultationFee): void
    {
        $selectedServices = $get('selected_services') ?? [];
        
        // Find or create a consultation service
        $consultationService = Service::firstOrCreate(
            ['name' => 'Medical Consultation'],
            [
                'description' => 'Medical consultation fee',
                'price' => $consultationFee,
                'is_active' => true,
                'category' => 'Consultation'
            ]
        );

        if ($consultationFee > 0) {
            // Update the service price to match the current consultation fee
            if ($consultationService->price != $consultationFee) {
                $consultationService->update(['price' => $consultationFee]);
            }

            // Auto-select the consultation service if not already selected
            if (!in_array($consultationService->id, $selectedServices)) {
                $selectedServices[] = $consultationService->id;
                $set('selected_services', $selectedServices);
            }
        } else {
            // Remove consultation service if fee is 0
            $selectedServices = array_filter($selectedServices, function($serviceId) use ($consultationService) {
                return $serviceId != $consultationService->id;
            });
            $set('selected_services', array_values($selectedServices));
        }
    }

    /**
     * CRITICAL: Safe invoice total calculation
     * This method ensures accurate financial calculations
     */
    private static function updateInvoiceTotal(Set $set, Get $get): void
    {
        try {
            // Initialize total
            $totalAmount = 0;

            // 1. Calculate drug items total
            $drugItems = $get('drug_items') ?? [];
            if (is_array($drugItems) && !empty($drugItems)) {
                foreach ($drugItems as $item) {
                    if (isset($item['line_total']) && is_numeric($item['line_total'])) {
                        $totalAmount += floatval($item['line_total']);
                    }
                }
            }

            // 2. Calculate services total (includes consultation for visit invoices)
            $type = $get('invoiceable_type');
            if ($type === Visit::class) {
                $selectedServices = $get('selected_services') ?? [];
                if (is_array($selectedServices) && !empty($selectedServices)) {
                    foreach ($selectedServices as $serviceId) {
                        $service = Service::find($serviceId);
                        if ($service && $service->price) {
                            $totalAmount += floatval($service->price);
                        }
                    }
                }
            }

            // Round to 2 decimal places and set
            $totalAmount = round($totalAmount, 2);
            $set('total_amount', $totalAmount);

        } catch (\Exception $e) {
            // Log error but don't break the form
            Log::error('Invoice total calculation error: ' . $e->getMessage());
            $set('total_amount', 0);
        }
    }
}
