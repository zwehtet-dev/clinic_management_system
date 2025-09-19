<?php

namespace App\Filament\Pages;

use UnitEnum;
use Filament\Forms;
use App\Models\Setting;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Services\PrinterService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Tabs\Tab;
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

            // Operational Settings
            'auto_generate_reports' => Setting::get('auto_generate_reports'),
            'auto_print_invoices' => Setting::get('auto_print_invoices'),


            // Financial Settings
            'consultation_fee_default' => Setting::get('consultation_fee_default'),
            'invoice_terms' => Setting::get('invoice_terms'),

            // Printer Settings
            'default_printer_type' => config('printing.default_printer_type'),
            'thermal_connection_type' => config('printing.thermal_connection_type'),
            'thermal_printer_name' => config('printing.thermal_printer_name'),
            'thermal_device_path' => config('printing.thermal_device_path'),
            'thermal_network_host' => config('printing.thermal_network_host'),
            'thermal_network_port' => config('printing.thermal_network_port'),
            'regular_printer_name' => config('printing.regular_printer_name'),
            'network_printer_host' => config('printing.network_printer_host'),
            'network_printer_port' => config('printing.network_printer_port'),
            'auto_print_invoices' => config('printing.auto_print_invoices'),
            'print_copies' => config('printing.print_copies'),
            'enable_print_queue' => config('printing.enable_print_queue'),
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

                                        FileUpload::make('clinic_logo')
                                            ->label('Clinic Logo')
                                            ->image()
                                            ->directory('clinic')
                                            ->acceptedFileTypes(['image/png', 'image/jpg', 'image/jpeg'])
                                            ->maxSize(2048)
                                            ->helperText('Upload clinic logo (PNG, JPG - Max 2MB)'),
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

                        Tab::make('System Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([

                                Section::make('Automation Settings')
                                    ->schema([
                                        Toggle::make('auto_generate_reports')
                                            ->label('Auto Generate Daily Reports')
                                            ->helperText('Automatically generate daily reports each morning'),
                                    ])
                                    ->columns(2),
                                Section::make('Financial Settings')
                                    ->schema([
                                        TextInput::make('consultation_fee_default')
                                            ->label('Default Consultation Fee')
                                            ->numeric()
                                            ->suffix(' Ks')
                                            ->step(0.01)
                                            ->minValue(0),

                                        Textarea::make('invoice_terms')
                                            ->label('Default Invoice Terms')
                                            ->rows(3)
                                            ->columnSpanFull()
                                            ->placeholder('Payment terms and conditions...'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Printer Settings')
                            ->icon('heroicon-o-printer')
                            ->schema([
                                Action::make('test_printer')
                                    ->label('Test Printer')
                                    ->icon('heroicon-o-printer')
                                    ->color('info')
                                    ->action(function (PrinterService $printerService) {
                                        $success = $printerService->testPrinter($this->data);

                                        if ($success) {
                                            Notification::make()
                                                ->title('Printer test successful')
                                                ->success()
                                                ->send();
                                        } else {
                                            Notification::make()
                                                ->title('Printer test failed')
                                                ->danger()
                                                ->body('Please check your printer settings and connection.')
                                                ->send();
                                        }
                                    }),
                                Section::make('General Settings')
                                    ->schema([
                                        Select::make('default_printer_type')
                                            ->label('Default Printer Type')
                                            ->options([
                                                'thermal' => 'Thermal Receipt Printer',
                                                'regular' => 'Regular Printer (A4)',
                                                'network' => 'Network Printer',
                                            ])
                                            ->required()
                                            ->live(),

                                        Toggle::make('auto_print_invoices')
                                            ->label('Auto Print Invoices')
                                            ->helperText('Automatically print invoices when created'),

                                        TextInput::make('print_copies')
                                            ->label('Number of Copies')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->maxValue(5),

                                        Toggle::make('enable_print_queue')
                                            ->label('Enable Print Queue')
                                            ->helperText('Use background queue for printing (recommended)'),
                                    ])
                                    ->columns(2),

                                Section::make('Thermal Printer Settings')
                                    ->schema([
                                        Select::make('thermal_connection_type')
                                            ->label('Connection Type')
                                            ->options([
                                                'windows' => 'Windows Printer',
                                                'cups' => 'CUPS (Linux/macOS)',
                                                'file' => 'Direct Device File',
                                                'network' => 'Network Connection',
                                            ])
                                            ->required()
                                            ->live(),

                                        TextInput::make('thermal_printer_name')
                                            ->label('Printer Name')
                                            ->helperText('Windows printer name or CUPS printer name')
                                            ->visible(fn (Get $get): bool =>
                                                in_array($get('thermal_connection_type'), ['windows', 'cups'])),

                                        TextInput::make('thermal_device_path')
                                            ->label('Device Path')
                                            ->helperText('e.g., /dev/usb/lp0 or /dev/ttyUSB0')
                                            ->visible(fn (Get $get): bool =>
                                                $get('thermal_connection_type') === 'file'),

                                        TextInput::make('thermal_network_host')
                                            ->label('Network Host')
                                            ->helperText('IP address of network printer')
                                            ->visible(fn (Get $get): bool =>
                                                $get('thermal_connection_type') === 'network'),

                                        TextInput::make('thermal_network_port')
                                            ->label('Network Port')
                                            ->numeric()
                                            ->default(9100)
                                            ->visible(fn (Get $get): bool =>
                                                $get('thermal_connection_type') === 'network'),
                                    ])
                                    ->columns(2)
                                    ->visible(fn (Get $get): bool => $get('default_printer_type') === 'thermal'),

                                Section::make('Regular Printer Settings')
                                    ->schema([
                                        TextInput::make('regular_printer_name')
                                            ->label('Printer Name')
                                            ->helperText('System printer name for A4 printing'),
                                    ])
                                    ->visible(fn (Get $get): bool => $get('default_printer_type') === 'regular'),

                                Section::make('Network Printer Settings')
                                    ->schema([
                                        TextInput::make('network_printer_host')
                                            ->label('Host IP Address')
                                            ->required(),

                                        TextInput::make('network_printer_port')
                                            ->label('Port')
                                            ->numeric()
                                            ->default(9100)
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->visible(fn (Get $get): bool => $get('default_printer_type') === 'network'),
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
            if ($key === 'clinic_logo') {
                if ($value) {
                    // Handle logo upload
                    Setting::set('clinic_logo_path', $value, 'string', 'Path to clinic logo file');
                }
                continue;
            }

            $type = $this->getSettingType($key, $value);
            Setting::set($key, $value, $type);
        }

        $this->updateEnvVariables([
            'DEFAULT_PRINTER_TYPE' => $data['default_printer_type'],
            'THERMAL_CONNECTION_TYPE' => $data['thermal_connection_type'],
            'THERMAL_PRINTER_NAME' => $data['thermal_printer_name'],
            'THERMAL_DEVICE_PATH' => $data['thermal_device_path'],
            'THERMAL_NETWORK_HOST' => $data['thermal_network_host'],
            'THERMAL_NETWORK_PORT' => $data['thermal_network_port'],
            'REGULAR_PRINTER_NAME' => $data['regular_printer_name'],
            'NETWORK_PRINTER_HOST' => $data['network_printer_host'],
            'NETWORK_PRINTER_PORT' => $data['network_printer_port'],
            'AUTO_PRINT_INVOICES' => $data['auto_print_invoices'] ? 'true' : 'false',
            'PRINT_COPIES' => $data['print_copies'],
            'ENABLE_PRINT_QUEUE' => $data['enable_print_queue'] ? 'true' : 'false',
        ]);

        Artisan::call('config:cache');

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

    private function updateEnvVariables(array $variables): void
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        foreach ($variables as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        file_put_contents($envPath, $envContent);
    }
}
