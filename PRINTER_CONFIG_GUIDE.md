# 🖨️ Physical Printer Integration - Installation Guide

## 📦 Required Dependencies

### 1. Install PHP ESC/POS Library (for thermal printers)
composer require mike42/escpos-php

### 2. Install PDF Generation Library (for regular printers)
composer require barryvdh/laravel-dompdf

### 3. Update composer.json to include:
{
    "require": {
        "mike42/escpos-php": "^4.0",
        "barryvdh/laravel-dompdf": "^2.0"
    }
}

## 🔧 System Requirements

### For Windows:
- Windows 10/11 or Windows Server 2016+
- Printer drivers installed
- PowerShell execution enabled

### For Linux:
- CUPS printing system installed
- USB/Serial printer drivers
- Proper permissions for printer access

### For Network Printers:
- Network connectivity to printer
- Printer IP address and port (usually 9100)

## ⚙️ Environment Configuration

### Add to your .env file:
```bash
# Default printer type: thermal, regular, network
DEFAULT_PRINTER_TYPE=thermal

# Thermal printer settings
THERMAL_CONNECTION_TYPE=windows
THERMAL_PRINTER_NAME="POS-80"
THERMAL_DEVICE_PATH="/dev/usb/lp0"
THERMAL_NETWORK_HOST="192.168.1.100"
THERMAL_NETWORK_PORT=9100

# Regular printer settings
REGULAR_PRINTER_NAME="Microsoft Print to PDF"

# Network printer settings
NETWORK_PRINTER_HOST="192.168.1.100"
NETWORK_PRINTER_PORT=9100

# Print settings
AUTO_PRINT_INVOICES=false
PRINT_COPIES=1
ENABLE_PRINT_QUEUE=true

# Clinic information for printing
CLINIC_NAME="Your Medical Clinic"
CLINIC_ADDRESS="123 Main Street, City, State 12345"
CLINIC_PHONE="+1 (555) 123-4567"
CLINIC_EMAIL="info@yourclinic.com"
```

## 🚀 Installation Steps

### 1. Install Dependencies
```bash
composer require mike42/escpos-php barryvdh/laravel-dompdf
```

### 2. Publish DomPDF Configuration
```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### 3. Add Service Provider to config/app.php
```php
'providers' => [
    // ...
    Barryvdh\DomPDF\ServiceProvider::class,
],

'aliases' => [
    // ...
    'PDF' => Barryvdh\DomPDF\Facade\Pdf::class,
],
```

### 4. Create Required Files
```bash
# Create printer service
php artisan make:service PrinterService

# Create print controller
php artisan make:controller PrintController

# Create print job
php artisan make:job PrintInvoiceJob

# Create printer settings page
php artisan make:filament-page PrinterSettings

# Create configuration file
touch config/printing.php

# Create print command
php artisan make:command SetupClinicCommand
```

### 5. Set Up Queue Worker (for background printing)
```bash
# Add to supervisor configuration or run manually:
php artisan queue:work --queue=default

# For production, add to supervisor:
# /etc/supervisor/conf.d/clinic-worker.conf
[program:clinic-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
directory=/path/to/your/project
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
```

## 🖨️ Printer Setup Instructions

### Thermal Receipt Printers:

#### USB Connection (Windows):
1. Install printer driver
2. Set printer name in Windows (e.g., "POS-80")
3. Configure: `THERMAL_CONNECTION_TYPE=windows`
4. Configure: `THERMAL_PRINTER_NAME="POS-80"`

#### USB Connection (Linux):
1. Install CUPS: `sudo apt-get install cups`
2. Add printer: `sudo lpadmin -p POS80 -v usb://your-printer`
3. Configure: `THERMAL_CONNECTION_TYPE=cups`
4. Configure: `THERMAL_PRINTER_NAME="POS80"`

#### Network Connection:
1. Get printer IP address
2. Configure: `THERMAL_CONNECTION_TYPE=network`
3. Configure: `THERMAL_NETWORK_HOST="192.168.1.100"`
4. Configure: `THERMAL_NETWORK_PORT=9100`

#### Direct Device Connection (Linux):
1. Find device: `ls /dev/tty*` or `ls /dev/usb/lp*`
2. Set permissions: `sudo chmod 666 /dev/ttyUSB0`
3. Configure: `THERMAL_CONNECTION_TYPE=file`
4. Configure: `THERMAL_DEVICE_PATH="/dev/ttyUSB0"`

### Regular Printers (A4):

#### Windows:
1. Install printer driver
2. Add printer in Windows settings
3. Configure: `REGULAR_PRINTER_NAME="Your Printer Name"`

#### Linux:
1. Install CUPS and add printer
2. Configure: `REGULAR_PRINTER_NAME="your-printer"`

## 🧪 Testing Your Setup

### 1. Test from Admin Panel
- Go to Admin → System → Printer Settings
- Click "Test Printer"
- Check if test page prints

### 2. Test via Command Line
```bash
php artisan tinker

# Test thermal printer
$printerService = app(\App\Services\PrinterService::class);
$printerService->testPrinter(['printer_type' => 'thermal']);

# Test regular printer
$printerService->testPrinter(['printer_type' => 'regular']);
```

### 3. Test Invoice Printing
```bash
# Create and print a test invoice
php artisan tinker

$invoice = \App\Models\Invoice::first();
$printerService = app(\App\Services\PrinterService::class);
$printerService->printInvoice($invoice);
```

## 🔧 Troubleshooting

### Common Issues:

#### "Printer not found" Error:
- Check printer name spelling
- Verify printer is online and ready
- Check printer drivers installed
- For network printers, ping the IP address

#### Permission Denied (Linux):
```bash
# Add user to lp group
sudo usermod -a -G lp www-data

# Set device permissions
sudo chmod 666 /dev/ttyUSB0
# or for permanent solution:
echo 'SUBSYSTEM=="tty", ATTRS{idVendor}=="your_vendor", ATTRS{idProduct}=="your_product", MODE="0666"' | sudo tee /etc/udev/rules.d/99-printer.rules
sudo udevadm control --reload-rules
```

#### Network Printer Not Responding:
```bash
# Test connectivity
telnet printer_ip 9100

# Check firewall
sudo ufw allow out 9100
```

#### Queue Not Processing:
```bash
# Check queue status
php artisan queue:failed

# Restart queue worker
php artisan queue:restart

# Clear failed jobs
php artisan queue:flush
```

## 📝 Usage Examples

### Basic Printing:
```php
// Print immediately
$printerService = app(\App\Services\PrinterService::class);
$printerService->printInvoice($invoice);

// Queue for background printing
\App\Jobs\PrintInvoiceJob::dispatch($invoice);

// Print with options
\App\Jobs\PrintInvoiceJob::dispatch($invoice, [
    'printer_type' => 'thermal',
    'copies' => 2
]);
```

### From Filament Actions:
```php
Tables\Actions\Action::make('print')
    ->action(function (Invoice $record) {
        \App\Jobs\PrintInvoiceJob::dispatch($record);
        
        Notification::make()
            ->title('Invoice queued for printing')
            ->success()
            ->send();
    });
```

## 📊 Monitoring Print Jobs

### Check Print Status:
```bash
# View queue jobs
php artisan queue:work --verbose

# Check failed jobs
php artisan queue:failed

# Monitor logs
tail -f storage/logs/laravel.log | grep -i print
```

### Print Statistics:
You can add print tracking to your database by creating a `print_jobs` table:

```php
// Migration
Schema::create('print_jobs', function (Blueprint $table) {
    $table->id();
    $table->string('invoice_number');
    $table->string('printer_type');
    $table->string('status'); // queued, printing, completed, failed
    $table->json('printer_options')->nullable();
    $table->timestamp('printed_at')->nullable();
    $table->text('error_message')->nullable();
    $table->timestamps();
});
```

## 🎯 Advanced Features

### Auto-Print on Invoice Creation:
Set `AUTO_PRINT_INVOICES=true` in your .env file

### Multiple Printer Support:
Configure different printers for different purposes:
```php
// Print receipt to thermal printer
PrintInvoiceJob::dispatch($invoice, ['printer_type' => 'thermal']);

// Print detailed invoice to A4 printer
PrintInvoiceJob::dispatch($invoice, ['printer_type' => 'regular']);
```

### Custom Print Templates:
Create different templates for different printer types in your views:
- `resources/views/invoice/thermal.blade.php`
- `resources/views/invoice/regular.blade.php`
- `resources/views/invoice/pdf.blade.php`

This comprehensive printer integration supports:
✅ Thermal receipt printers (ESC/POS)
✅ Regular A4 printers 
✅ Network printers
✅ Background print queuing
✅ Multiple printer types
✅ Print testing and monitoring
✅ Automatic stock updates
✅ Custom print templates
✅ Error handling and logging
