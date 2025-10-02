<?php

namespace App\Filament\Resources\Visits\Schemas;

use App\Models\Drug;
use App\Models\Visit;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Service;
use App\Models\DrugBatch;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class VisitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Visit Information')
                    ->schema([
                        TextInput::make('public_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => Visit::generatePublicId()),
                        Select::make('patient_id')
                            ->label('Patient')
                            ->relationship('patient', 'name')
                            ->searchable()
                            ->preload(false)
                            ->getSearchResultsUsing(function (string $search) {
                                return Patient::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('public_id', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->orderBy('created_at', 'desc')
                                    ->get()
                                    ->mapWithKeys(function ($patient) {
                                        return [$patient->id => "{$patient->public_id} - {$patient->name}"];
                                    })
                                    ->toArray();
                            })
                            ->default(function(){
                                if(request()->exists('patient_id')){
                                    return request()->get('patient_id');
                                }
                                return null;
                            })
                            ->getOptionLabelUsing(function ($value, Get $get) {
                                $patient = Patient::find($value);
                                if($patient){
                                    return "{$patient->public_id} - {$patient->name}";
                                }

                                return '';

                            })
                            ->required(),
                        Select::make('doctor_id')
                            ->label('Doctor')
                            ->relationship('doctor', 'public_id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('visit_type')
                            ->options(['consultation' => 'Consultation', 'follow-up' => 'Follow up'])
                            ->default('consultation')
                            ->native(false)
                            ->required(),
                        TextInput::make('consultation_fee')
                            ->label('Consultation Fee')
                            ->default(10000)
                            ->suffix('Ks')
                            ->required()
                            ->numeric(),
                        DatePicker::make('visit_date')
                            ->default(now())
                            ->required(),

                        Select::make('status')
                            ->options(['pending' => 'Pending', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])
                            ->default('completed')
                            ->native(false)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Medical Details')
                    ->schema([
                        Textarea::make('diagnosis')
                            ->rows(4)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->rows(4)
                            ->placeholder('Treatment notes, prescriptions, recommendations...')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),

                // Section::make('Invoice & Services')
                //     ->schema([
                //         Toggle::make('create_invoice')
                //             ->label('Create Invoice for this visit')
                //             ->default(false)
                //             ->live()
                //             ->columnSpanFull(),

                //         Group::make()
                //             ->relationship('invoice')
                //             ->schema([
                //                 TextInput::make('invoice.invoice_number')
                //                     ->label('Invoice Number')
                //                     ->unique(
                //                         table: 'invoices',
                //                         column: 'invoice_number',
                //                         ignoreRecord: true
                //                     )
                //                     ->default(function(){
                //                         return Invoice::generateInvoiceNumber();
                //                     })
                //                     ->disabled(fn (string $operation): bool => $operation === 'edit')
                //                     ->required()
                //                     ->visible(fn (Get $get): bool => $get('create_invoice') === true),

                //                 DatePicker::make('invoice.invoice_date')
                //                     ->default(now())
                //                     ->required()
                //                     ->visible(fn (Get $get): bool => $get('create_invoice') === true),

                //                 // Drug Items Repeater
                //                 Repeater::make('drug_items')
                //                     ->label('Drugs/Medicines')
                //                     ->relationship('invoice.invoiceItems')
                //                     ->schema([
                //                         // Combined search for drug name, generic name, or batch number
                //                         Select::make('search_term')
                //                             ->label('Search Drug or Batch')
                //                             ->searchable()
                //                             ->preload(false)
                //                             ->getSearchResultsUsing(function (string $search) {
                //                                 // Search by drug name, generic name, or batch number
                //                                 $drugResults = Drug::active()
                //                                     ->where(function($query) use ($search) {
                //                                         $query->where('name', 'like', "%{$search}%")
                //                                             ->orWhere('generic_name', 'like', "%{$search}%");
                //                                     })
                //                                     ->with('drugForm')
                //                                     ->limit(25)
                //                                     ->get()
                //                                     ->mapWithKeys(fn ($drug) => [
                //                                         "drug_{$drug->id}" => "{$drug->name} - {$drug->generic_name} ({$drug->drugForm->name}) - Stock: {$drug->total_stock}"
                //                                     ]);

                //                                 $batchResults = DrugBatch::with('drug')
                //                                     ->where('batch_number', 'like', "%{$search}%")
                //                                     ->where('quantity_available', '>', 0)
                //                                     ->where('expiry_date', '>', now())
                //                                     ->limit(25)
                //                                     ->get()
                //                                     ->mapWithKeys(function ($batch) {
                //                                         $expiryWarning = $batch->expiry_date->diffInDays() <= 30 ? ' ⚠️' : '';
                //                                         return [
                //                                             "batch_{$batch->id}" => "📦 Batch: {$batch->batch_number} - {$batch->drug->name} (Available: {$batch->quantity_available}) - Exp: {$batch->expiry_date->format('M d, Y')}{$expiryWarning}"
                //                                         ];
                //                                     });

                //                                 return $drugResults->merge($batchResults)->toArray();
                //                             })
                //                             ->afterStateUpdated(function (Set $set, Get $get, $state) {
                //                                 if (!$state) return;

                //                                 if (str_starts_with($state, 'drug_')) {
                //                                     // Selected a drug - show batch options
                //                                     $drugId = str_replace('drug_', '', $state);
                //                                     $drug = Drug::find($drugId);

                //                                     if ($drug) {
                //                                         $set('selected_drug_id', $drugId);
                //                                         $set('invoice.invoiceItems.unit_price', $drug->price);
                //                                         $set('invoice.invoiceItems.itemable_type', DrugBatch::class);

                //                                         // Get available batches for this drug
                //                                         $batches = DrugBatch::where('drug_id', $drugId)
                //                                             ->where('quantity_available', '>', 0)
                //                                             ->where('expiry_date', '>', now())
                //                                             ->orderBy('expiry_date')
                //                                             ->get()
                //                                             ->mapWithKeys(function ($batch) {
                //                                                 $expiryWarning = $batch->expiry_date->diffInDays() <= 30 ? ' ⚠️' : '';
                //                                                 return [
                //                                                     $batch->id => "Batch: {$batch->batch_number} (Stock: {$batch->quantity_available}) - Exp: {$batch->expiry_date->format('M d, Y')}{$expiryWarning}"
                //                                                 ];
                //                                             });

                //                                         $set('available_batches', $batches->toArray());

                //                                         // If only one batch, auto-select it
                //                                         if ($batches->count() === 1) {
                //                                             $batchId = $batches->keys()->first();
                //                                             $set('invoice.invoiceItems.itemable_id', $batchId);
                //                                             $batch = DrugBatch::find($batchId);
                //                                             if ($batch && $batch->sell_price) {
                //                                                 $set('invoice.invoiceItems.unit_price', $batch->sell_price);
                //                                             }
                //                                         }
                //                                     }
                //                                 } elseif (str_starts_with($state, 'batch_')) {
                //                                     // Selected a batch directly
                //                                     $batchId = str_replace('batch_', '', $state);
                //                                     $batch = DrugBatch::with('drug')->find($batchId);

                //                                     if ($batch) {
                //                                         $set('selected_drug_id', $batch->drug_id);
                //                                         $set('invoice.invoiceItems.itemable_id', $batchId);
                //                                         $set('invoice.invoiceItems.itemable_type', DrugBatch::class);
                //                                         $set('invoice.invoiceItems.unit_price', $batch->sell_price ?? $batch->drug->price);
                //                                         $set('available_batches', []);
                //                                     }
                //                                 }
                //                             })
                //                             ->getOptionLabelUsing(function ($value): ?string {
                //                                 if (str_starts_with($value, 'drug_')) {
                //                                     $id = str_replace('drug_', '', $value);
                //                                     $drug = Drug::with('drugForm')->find($id);
                //                                     return $drug ? "{$drug->name} - {$drug->generic_name} ({$drug->drugForm->name})" : null;
                //                                 }

                //                                 if (str_starts_with($value, 'batch_')) {
                //                                     $id = str_replace('batch_', '', $value);
                //                                     $batch = DrugBatch::with('drug')->find($id);
                //                                     return $batch ? "📦 {$batch->batch_number} - {$batch->drug->name}" : null;
                //                                 }

                //                                 return null;
                //                             }),
                //                             // ->getOptionLabelUsing(fn ($value): ?string =>
                //                             //     Drug::find($value)?->name
                //                             // ),

                //                         // Batch selection (shows when drug is selected and has multiple batches)
                //                         Select::make('invoice.invoiceItems.itemable_id')
                //                             ->label('Select Batch')
                //                             ->options(function (Get $get) {
                //                                 return $get('available_batches') ?? [];
                //                             })
                //                             ->visible(fn (Get $get) => !empty($get('available_batches')))
                //                             ->required(fn (Get $get) => !empty($get('available_batches')))
                //                             ->live()
                //                             ->afterStateUpdated(function (Set $set, Get $get, $state) {
                //                                 if ($state) {
                //                                     $batch = DrugBatch::find($state);
                //                                     if ($batch && $batch->sell_price) {
                //                                         $set('invoice.invoiceItems.unit_price', $batch->sell_price);
                //                                     }
                //                                 }
                //                             }),

                //                         // Hidden fields for form processing
                //                         TextInput::make('invoice.invoiceItems.itemable_type')
                //                             ->default(DrugBatch::class)
                //                             ->hidden()
                //                             ->dehydrated(),

                //                         TextInput::make('selected_drug_id')
                //                             ->hidden()
                //                             ->dehydrated(false),

                //                         TextInput::make('available_batches')
                //                             ->hidden()
                //                             ->dehydrated(false),

                //                         TextInput::make('invoice.invoiceItems.quantity')
                //                             ->numeric()
                //                             ->default(1)
                //                             ->required()
                //                             ->minValue(1)
                //                             ->live()
                //                             ->afterStateUpdated(function (Set $set, Get $get, $state) {
                //                                 $unitPrice = floatval($get('invoice.invoiceItems.unit_price') ?? 0);
                //                                 $quantity = floatval($state ?? 0);
                //                                 $set('invoice.invoiceItems.line_total', $unitPrice * $quantity);

                //                                 // Update invoice total
                //                                 self::updateInvoiceTotal($set, $get);
                //                             })
                //                             ->maxValue(function(Get $get) {
                //                                 $batchId = $get('invoice.invoiceItems.itemable_id');
                //                                 if ($batchId) {
                //                                     $batch = DrugBatch::find($batchId);
                //                                     return $batch ? $batch->quantity_available : 999;
                //                                 }
                //                                 return 999;
                //                             })
                //                             ->helperText(function(Get $get) {
                //                                 $batchId = $get('invoice.invoiceItems.itemable_id');
                //                                 if ($batchId) {
                //                                     $batch = DrugBatch::find($batchId);
                //                                     if ($batch) {
                //                                         $expiryDays = (int) $batch->expiry_date->diffInDays();
                //                                         $stockInfo = "Available: {$batch->quantity_available}";
                //                                         $expiryInfo = $expiryDays <= 30 ? " ⚠️ Expires in {$expiryDays} days" : "";
                //                                         return $stockInfo . $expiryInfo;
                //                                     }
                //                                 }
                //                                 return '';
                //                             }),

                //                         TextInput::make('invoice.invoiceItems.unit_price')
                //                             ->label('Unit Price')
                //                             ->numeric()
                //                             ->suffix('Ks')
                //                             ->required()
                //                             ->live()
                //                             ->afterStateUpdated(function (Set $set, Get $get, $state) {
                //                                 $unitPrice = floatval($state ?? 0);
                //                                 $quantity = floatval($get('invoice.invoiceItems.quantity') ?? 0);
                //                                 $set('invoice.invoiceItems.line_total', $unitPrice * $quantity);

                //                                 // Update invoice total
                //                                 self::updateInvoiceTotal($set, $get);
                //                             }),

                //                         TextInput::make('invoice.invoiceItems.line_total')
                //                             ->label('Total')
                //                             ->numeric()
                //                             ->suffix('Ks')
                //                             ->disabled()
                //                             ->dehydrated(),
                //                     ])
                //                     ->columns(5)
                //                     ->reorderable(false)
                //                     ->cloneable()
                //                     ->addActionLabel('Add Drug')
                //                     ->visible(fn (Get $get): bool => $get('create_invoice') === true)
                //                     ->columnSpanFull()
                //                     ->live()
                //                     ->afterStateUpdated(function (Set $set, Get $get) {
                //                         self::updateInvoiceTotal($set, $get);
                //                     }),

                //                 // Services as checkboxes
                //                 CheckboxList::make('selected_services')
                //                     ->relationship('invoice.invoiceItems')
                //                     ->label('Select Services')
                //                     ->options(function () {
                //                         return Service::where('is_active', true)
                //                             ->orderBy('name')
                //                             ->get()
                //                             ->mapWithKeys(fn ($service) => [
                //                                 $service->id => "{$service->name} - {$service->price} Ks"
                //                             ])
                //                             ->toArray();
                //                     })
                //                     ->columns(3)
                //                     ->visible(fn (Get $get) => $get('create_invoice') === true)
                //                     ->live()
                //                     ->afterStateUpdated(function (Set $set, Get $get, $state) {
                //                         self::updateInvoiceTotal($set, $get);
                //                     })
                //                     ->columnSpanFull(),

                //                 TextInput::make('invoice.total_amount')
                //                     ->label('Total Amount')
                //                     ->numeric()
                //                     ->suffix(' Ks')
                //                     ->disabled()
                //                     ->dehydrated()
                //                     ->visible(fn (Get $get): bool => $get('create_invoice') === true),

                //                 Textarea::make('invoice.invoice_notes')
                //                     ->label('Invoice Notes')
                //                     ->rows(3)
                //                     ->placeholder('Additional notes for the invoice...')
                //                     ->visible(fn (Get $get): bool => $get('create_invoice') === true)
                //                     ->columnSpanFull(),
                //         ])
                //     ])
                //     ->columns(2)
                //     ->collapsible()
                //     ->collapsed()
                //     ->columnSpanFull(),
            ]);
    }

    /**
     * Helper method to update invoice total
     */
    private static function updateInvoiceTotal(Set $set, Get $get): void
    {
        $consultationFee = floatval($get('consultation_fee') ?? 0);

        // Calculate drug items total
        $drugItems = $get('drug_items') ?? [];
        $drugTotal = collect($drugItems)->sum('invoice.invoiceItems.line_total');

        // Calculate services total
        $selectedServices = $get('selected_services') ?? [];
        $servicesTotal = 0;
        foreach ($selectedServices as $serviceId) {
            $service = Service::find($serviceId);
            if ($service) {
                $servicesTotal += $service->price;
            }
        }

        $totalAmount = $consultationFee + $drugTotal + $servicesTotal;
        $set('invoice.total_amount', $totalAmount);
    }
}
