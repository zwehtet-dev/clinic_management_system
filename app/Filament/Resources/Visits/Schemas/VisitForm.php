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
                            ->default(fn()=>Service::where('name','Consulation Fee')->price ?? 0)
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
                                        return $get('invoice.invoice_items.itemable_id_options') ?? [];
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

                        // Services as checkboxes
                        CheckboxList::make('selected_services')
                            ->label('Select Services')
                            ->options(function () {
                                return Service::where('is_active', true)->pluck('name', 'id');
                            })
                            ->columns(3)
                            ->visible(fn (Get $get) => $get('create_invoice') === true)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                // Dynamically add service items to invoice_items
                                $invoiceItems = $get('invoice.invoice_items') ?? [];
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
                                $set('invoice.invoice_items', $invoiceItems);
                            }),

                        TextInput::make('invoice.total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->suffix(' Ks')
                            ->disabled()
                            ->dehydrated()
                            ->live()
                            ->afterStateHydrated(function (Set $set, Get $get) {
                                $cs = $get('consultation_fee') ?? 0;
                                $items = $get('invoice.invoice_items') ?? [];
                                $total = collect($items)->sum('line_total');
                                $set('invoice.total_amount', $total + $cs);
                            })
                            ->visible(fn (Get $get): bool => $get('create_invoice') === true),



                        // Select::make('selected_services')
                        //     ->label('Select Services')
                        //     ->options(function () {
                        //         return Service::where('is_active', true)->pluck('name', 'id');
                        //     })
                        //     ->searchable()
                        //     ->multiple() // allows selecting multiple services
                        //     ->createOptionForm([
                        //         TextInput::make('name')->required(),
                        //         TextInput::make('price')->numeric()->required()->suffix('Ks'),
                        //         Textarea::make('description')->rows(2),
                        //     ])
                        //     ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        //         // Dynamically add selected services to invoice_items
                        //         $invoiceItems = $get('invoice.invoice_items') ?? [];
                        //         foreach ($state as $serviceId) {
                        //             $service = Service::find($serviceId);
                        //             if ($service && !collect($invoiceItems)->contains('itemable_id', $service->id)) {
                        //                 $invoiceItems[] = [
                        //                     'itemable_type' => Service::class,
                        //                     'itemable_id' => $service->id,
                        //                     'quantity' => 1,
                        //                     'unit_price' => $service->price,
                        //                     'line_total' => $service->price,
                        //                 ];
                        //             }
                        //         }
                        //         $set('invoice.invoice_items', $invoiceItems);
                        //     })
                        //     ->columnSpanFull(),

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
