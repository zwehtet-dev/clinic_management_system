<?php

namespace App\Filament\Pages;

use UnitEnum;
use Filament\Forms;
use App\Models\Setting;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Services\PrinterService;
use App\Exports\DrugsTemplateExport;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Illuminate\Support\Facades\Cache;
use App\Exports\DoctorsTemplateExport;
use Filament\Forms\Contracts\HasForms;
use App\Exports\ServicesTemplateExport;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Tabs\Tab;
use App\Exports\DrugBatchesTemplateExport;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Concerns\InteractsWithForms;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'App Settings';
    protected static string|UnitEnum|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.settings-page';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // App Information
            'app_name' => Setting::get('app_name'),
            'clinic_name' => Setting::get('clinic_name'),
            'clinic_address' => Setting::get('clinic_address'),
            'clinic_phone' => Setting::get('clinic_phone'),
            'clinic_email' => Setting::get('clinic_email'),

            // Financial Settings
            'consultation_fee_default' => Setting::get('consultation_fee_default'),
            'invoice_terms' => Setting::get('invoice_terms'),
            'currency_symbol' => Setting::get('currency_symbol'),

            // Print Settings
            'auto_open_print_window' => Setting::get('auto_open_print_window'),
            'print_logo' => Setting::get('print_logo'),
            'print_format' => Setting::get('print_format'),
            'thermal_max_items' => Setting::get('thermal_max_items'),
            'receipt_max_items' => Setting::get('receipt_max_items'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Clinic Information')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Section::make('Basic Information')
                                    ->schema([
                                        TextInput::make('app_name')
                                            ->label('Application Name')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('clinic_name')
                                            ->label('Clinic Name')
                                            ->required()
                                            ->maxLength(255),

                                        // FileUpload::make('clinic_logo')
                                        //     ->label('Clinic Logo')
                                        //     ->image()
                                        //     ->directory('clinic')
                                        //     ->acceptedFileTypes(['image/png', 'image/jpg', 'image/jpeg'])
                                        //     ->maxSize(2048)
                                        //     ->helperText('Upload clinic logo (PNG, JPG - Max 2MB)'),
                                    ])
                                    ->columns(2),

                                Section::make('Contact Information')
                                    ->schema([
                                        Textarea::make('clinic_address')
                                            ->label('Clinic Address')
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        TextInput::make('clinic_phone')
                                            ->label('Phone Number')
                                            ->tel()
                                            ->maxLength(255),

                                        TextInput::make('clinic_email')
                                            ->label('Email Address')
                                            ->email()
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Financial Settings')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Section::make('Default Fees & Currency')
                                    ->schema([
                                        TextInput::make('consultation_fee_default')
                                            ->label('Default Consultation Fee')
                                            ->numeric()
                                            ->suffix(' Ks')
                                            ->step(0.01)
                                            ->minValue(0),

                                        TextInput::make('currency_symbol')
                                            ->label('Currency Symbol')
                                            ->default('Ks')
                                            ->maxLength(10),

                                        Textarea::make('invoice_terms')
                                            ->label('Default Invoice Terms')
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->placeholder('Payment terms and conditions...'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Print Settings')
                            ->icon('heroicon-o-printer')
                            ->schema([
                                Section::make('Print Options')
                                    ->description('Configure printing behavior for invoices')
                                    ->schema([
                                        Select::make('print_format')
                                            ->label('Default Print Format')
                                            ->options([
                                                'receipt' => 'Receipt (80mm)',
                                                'thermal' => 'Thermal Receipt (58mm)',
                                                'a4' => 'A4 Invoice',
                                            ])
                                            ->default('receipt')
                                            ->helperText('Choose the default format for printing invoices'),

                                        Toggle::make('auto_open_print_window')
                                            ->label('Auto-open Print Window')
                                            ->helperText('Automatically open print window when invoice is created'),

                                        Toggle::make('print_logo')
                                            ->label('Include Logo on Receipts')
                                            ->helperText('Show clinic logo on printed receipts'),

                                        TextInput::make('thermal_max_items')
                                            ->label('Thermal Receipt - Max Detailed Items')
                                            ->numeric()
                                            ->default(12)
                                            ->minValue(5)
                                            ->maxValue(20)
                                            ->helperText('Maximum items to show in detail on thermal receipts'),

                                        TextInput::make('receipt_max_items')
                                            ->label('Standard Receipt - Max Detailed Items')
                                            ->numeric()
                                            ->default(15)
                                            ->minValue(8)
                                            ->maxValue(25)
                                            ->helperText('Maximum items to show in detail on standard receipts'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Import Templates')
                            ->icon('heroicon-o-document-arrow-down')
                            ->schema([
                                Section::make('Download Import Templates')
                                    ->description('Download Excel templates for importing data into the system')
                                    ->schema([
                                        Placeholder::make('drugs_template')
                                            ->label('Drugs Template')
                                            ->content('Download the Excel template for importing drugs with all required fields and sample data.')
                                            ->suffixAction(
                                                Action::make('download_drugs_template')
                                                    ->label('Download')
                                                    ->icon('heroicon-o-document-arrow-down')
                                                    ->color('primary')
                                                    ->action(function () {
                                                        return Excel::download(new DrugsTemplateExport, 'drugs_template.xlsx');
                                                    })
                                            ),

                                        Placeholder::make('drug_batches_template')
                                            ->label('Drug Batches Template')
                                            ->content('Download the Excel template for importing drug batches with batch numbers, prices, and quantities.')
                                            ->suffixAction(
                                                Action::make('download_drug_batches_template')
                                                    ->label('Download')
                                                    ->icon('heroicon-o-document-arrow-down')
                                                    ->color('primary')
                                                    ->action(function () {
                                                        return Excel::download(new DrugBatchesTemplateExport, 'drug_batches_template.xlsx');
                                                    })
                                            ),

                                        Placeholder::make('doctors_template')
                                            ->label('Doctors Template')
                                            ->content('Download the Excel template for importing doctors with their specializations and contact information.')
                                            ->suffixAction(
                                                Action::make('download_doctors_template')
                                                    ->label('Download')
                                                    ->icon('heroicon-o-document-arrow-down')
                                                    ->color('primary')
                                                    ->action(function () {
                                                        return Excel::download(new DoctorsTemplateExport, 'doctors_template.xlsx');
                                                    })
                                            ),

                                        Placeholder::make('services_template')
                                            ->label('Services Template')
                                            ->content('Download the Excel template for importing services with names, descriptions, and prices.')
                                            ->suffixAction(
                                                Action::make('download_services_template')
                                                    ->label('Download')
                                                    ->icon('heroicon-o-document-arrow-down')
                                                    ->color('primary')
                                                    ->action(function () {
                                                        return Excel::download(new ServicesTemplateExport, 'services_template.xlsx');
                                                    })
                                            ),
                                    ])
                                    ->columns(1),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action(function () {
                    $this->saveSettings();
                }),

            Action::make('reset_defaults')
                ->label('Reset to Defaults')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->action(function () {
                    Setting::initializeDefaults();
                    $this->mount(); // Reload form data

                    Notification::make()
                        ->title('Settings reset to defaults')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function saveSettings(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            // if ($key === 'clinic_logo') {
            //     if ($value) {
            //         // Handle logo upload
            //         Setting::set('clinic_logo_path', $value, 'string', 'Path to clinic logo file');
            //     }
            //     continue;
            // }

            $type = $this->getSettingType($key, $value);
            Setting::set($key, $value, $type);
        }

        // No need to update env variables for database-stored settings

        // Clear all setting caches
        Cache::flush();

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    private function getSettingType(string $key, $value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_numeric($value) && strpos($value, '.') !== false) {
            return 'float';
        }

        if (is_numeric($value)) {
            return 'integer';
        }

        if (in_array($key, ['clinic_address', 'invoice_terms'])) {
            return 'text';
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return 'url';
        }

        return 'string';
    }


}
