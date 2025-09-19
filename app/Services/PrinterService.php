<?php

namespace App\Services;

use App\Models\Invoice;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class PrinterService
{
    private $printerSettings;

    public function __construct()
    {
        $this->printerSettings = config('printing');
    }

    /**
     * Print invoice to physical printer
     */
    public function printInvoice(Invoice $invoice, array $options = []): bool
    {
        try {
            $printerType = $options['printer_type'] ?? $this->printerSettings['default_printer_type'];

            switch ($printerType) {
                case 'thermal':
                    return $this->printThermalInvoice($invoice, $options);
                case 'regular':
                    return $this->printRegularInvoice($invoice, $options);
                case 'network':
                    return $this->printNetworkInvoice($invoice, $options);
                default:
                    throw new Exception("Unsupported printer type: {$printerType}");
            }
        } catch (Exception $e) {
            Log::error('Printer error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Print to thermal receipt printer (POS printers)
     */
    private function printThermalInvoice(Invoice $invoice, array $options): bool
    {
        try {
            $connector = $this->getThermalConnector($options);
            $printer = new Printer($connector);

            // Print header
            $this->printThermalHeader($printer);

            // Print invoice details
            $this->printThermalInvoiceDetails($printer, $invoice);

            // Print items
            $this->printThermalItems($printer, $invoice);

            // Print total
            $this->printThermalTotal($printer, $invoice);

            // Print footer
            $this->printThermalFooter($printer, $invoice);

            // Cut paper and close
            $printer->cut();
            $printer->close();

            return true;
        } catch (Exception $e) {
            Log::error('Thermal printer error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Print to regular printer (A4 format)
     */
    private function printRegularInvoice(Invoice $invoice, array $options): bool
    {
        try {
            // Generate PDF and send to printer
            $pdfContent = $this->generateInvoicePDF($invoice);
            $tempFile = tempnam(sys_get_temp_dir(), 'invoice_') . '.pdf';
            file_put_contents($tempFile, $pdfContent);

            $printerName = $options['printer_name'] ?? $this->printerSettings['regular_printer_name'];

            // Print using system command (Windows/Linux compatible)
            if (PHP_OS_FAMILY === 'Windows') {
                $command = "powershell -Command \"Start-Process -FilePath '{$tempFile}' -Verb Print -WindowStyle Hidden\"";
            } else {
                $command = "lp -d {$printerName} {$tempFile}";
            }

            exec($command, $output, $returnCode);
            unlink($tempFile);

            return $returnCode === 0;
        } catch (Exception $e) {
            Log::error('Regular printer error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Print to network printer
     */
    private function printNetworkInvoice(Invoice $invoice, array $options): bool
    {
        try {
            $host = $options['host'] ?? $this->printerSettings['network_printer_host'];
            $port = $options['port'] ?? $this->printerSettings['network_printer_port'];

            $connector = new NetworkPrintConnector($host, $port);
            $printer = new Printer($connector);

            // Use thermal format for network printers (most are thermal)
            $this->printThermalHeader($printer);
            $this->printThermalInvoiceDetails($printer, $invoice);
            $this->printThermalItems($printer, $invoice);
            $this->printThermalTotal($printer, $invoice);
            $this->printThermalFooter($printer, $invoice);

            $printer->cut();
            $printer->close();

            return true;
        } catch (Exception $e) {
            Log::error('Network printer error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get thermal printer connector
     */
    private function getThermalConnector(array $options)
    {
        $connectionType = $options['connection_type'] ?? $this->printerSettings['thermal_connection_type'];

        switch ($connectionType) {
            case 'windows':
                $printerName = $options['printer_name'] ?? $this->printerSettings['thermal_printer_name'];
                return new WindowsPrintConnector($printerName);

            case 'cups':
                $printerName = $options['printer_name'] ?? $this->printerSettings['thermal_printer_name'];
                return new CupsPrintConnector($printerName);

            case 'file':
                $devicePath = $options['device_path'] ?? $this->printerSettings['thermal_device_path'];
                return new FilePrintConnector($devicePath);

            case 'network':
                $host = $options['host'] ?? $this->printerSettings['thermal_network_host'];
                $port = $options['port'] ?? $this->printerSettings['thermal_network_port'];
                return new NetworkPrintConnector($host, $port);

            default:
                throw new Exception("Unsupported connection type: {$connectionType}");
        }
    }

    /**
     * Print thermal receipt header
     */
    private function printThermalHeader(Printer $printer): void
    {
        $clinicName = config('app.clinic_name', 'Medical Clinic');
        $clinicAddress = config('app.clinic_address', '');
        $clinicPhone = config('app.clinic_phone', '');

        // Try to print logo if available
        try {
            $logoPath = storage_path('app/public/clinic-logo.png');
            if (file_exists($logoPath)) {
                $logo = EscposImage::load($logoPath, false);
                $printer->bitImage($logo);
                $printer->feed();
            }
        } catch (Exception $e) {
            Log::warning('Could not print logo: ' . $e->getMessage());
        }

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->text($clinicName . "\n");
        $printer->selectPrintMode();

        if ($clinicAddress) {
            $printer->text($clinicAddress . "\n");
        }
        if ($clinicPhone) {
            $printer->text("Tel: " . $clinicPhone . "\n");
        }

        $printer->feed();
        $printer->text(str_repeat("=", 32) . "\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
    }

    /**
     * Print thermal invoice details
     */
    private function printThermalInvoiceDetails(Printer $printer, Invoice $invoice): void
    {
        $printer->text("INVOICE: " . $invoice->invoice_number . "\n");
        $printer->text("Date: " . $invoice->invoice_date->format('M d, Y H:i') . "\n");
        $printer->text("Patient: " . $invoice->patient->name . "\n");

        if ($invoice->patient->phone) {
            $printer->text("Phone: " . $invoice->patient->phone . "\n");
        }

        if ($invoice->invoiceable_type === 'App\\Models\\Visit') {
            $visit = $invoice->invoiceable;
            $printer->text("Doctor: Dr. " . $visit->doctor->name . "\n");
            $printer->text("Visit: " . $visit->public_id . "\n");
        }

        $printer->text(str_repeat("-", 32) . "\n");
    }

    /**
     * Print thermal invoice items
     */
    private function printThermalItems(Printer $printer, Invoice $invoice): void
    {
        foreach ($invoice->invoiceItems as $item) {
            $itemName = $item->itemable->name;
            $quantity = $item->quantity;
            $unitPrice = $item->unit_price;
            $lineTotal = $item->line_total;

            // Item name (truncate if too long)
            $printer->text($this->truncateText($itemName, 30) . "\n");

            // Quantity x Price = Total
            $qtyPriceText = sprintf("%d x $%.2f", $quantity, $unitPrice);
            $totalText = sprintf("$%.2f", $lineTotal);
            $padding = 32 - strlen($qtyPriceText) - strlen($totalText);

            $printer->text($qtyPriceText . str_repeat(" ", $padding) . $totalText . "\n");
        }

        $printer->text(str_repeat("-", 32) . "\n");
    }

    /**
     * Print thermal total
     */
    private function printThermalTotal(Printer $printer, Invoice $invoice): void
    {
        $totalText = "TOTAL: $" . number_format($invoice->total_amount, 2);
        $padding = 32 - strlen($totalText);

        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->text(str_repeat(" ", $padding) . $totalText . "\n");
        $printer->selectPrintMode();

        $printer->text(str_repeat("=", 32) . "\n");
    }

    /**
     * Print thermal footer
     */
    private function printThermalFooter(Printer $printer, Invoice $invoice): void
    {
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("Thank you for your visit!\n");

        if ($invoice->notes) {
            $printer->feed();
            $printer->text("Notes:\n");
            $printer->text($this->wrapText($invoice->notes, 32) . "\n");
        }

        $printer->feed();
        $printer->text("Printed: " . now()->format('M d, Y H:i') . "\n");
        $printer->feed(3);
        $printer->setJustification(Printer::JUSTIFY_LEFT);
    }

    /**
     * Generate PDF for regular printers
     */
    private function generateInvoicePDF(Invoice $invoice): string
    {
        $html = view('invoice.pdf', compact('invoice'))->render();

        // Use DomPDF or similar library
        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->output();
    }

    /**
     * Test printer connection
     */
    public function testPrinter(array $options = []): bool
    {
        try {
            $printerType = $options['printer_type'] ?? $this->printerSettings['default_printer_type'];

            switch ($printerType) {
                case 'thermal':
                    $connector = $this->getThermalConnector($options);
                    $printer = new Printer($connector);

                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->text("=== PRINTER TEST ===\n");
                    $printer->text("Connection: OK\n");
                    $printer->text("Date: " . now()->format('M d, Y H:i') . "\n");
                    $printer->text("===================\n");
                    $printer->feed(3);
                    $printer->cut();
                    $printer->close();

                    return true;

                case 'regular':
                    // Test regular printer by printing a simple test page
                    $testContent = "Printer Test - " . now()->format('M d, Y H:i');
                    $tempFile = tempnam(sys_get_temp_dir(), 'test_print_');
                    file_put_contents($tempFile, $testContent);

                    $printerName = $options['printer_name'] ?? $this->printerSettings['regular_printer_name'];

                    if (PHP_OS_FAMILY === 'Windows') {
                        $command = "print /D:{$printerName} {$tempFile}";
                    } else {
                        $command = "lp -d {$printerName} {$tempFile}";
                    }

                    exec($command, $output, $returnCode);
                    unlink($tempFile);

                    return $returnCode === 0;

                default:
                    return false;
            }
        } catch (Exception $e) {
            Log::error('Printer test error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available printers
     */
    public function getAvailablePrinters(): array
    {
        $printers = [];

        try {
            if (PHP_OS_FAMILY === 'Windows') {
                // Get Windows printers
                $output = shell_exec('wmic printer get name /format:csv');
                $lines = explode("\n", $output);

                foreach ($lines as $line) {
                    if (strpos($line, ',') !== false) {
                        $parts = explode(',', $line);
                        if (isset($parts[1]) && !empty(trim($parts[1]))) {
                            $printers[] = [
                                'name' => trim($parts[1]),
                                'type' => 'windows'
                            ];
                        }
                    }
                }
            } else {
                // Get CUPS printers (Linux/macOS)
                $output = shell_exec('lpstat -p 2>/dev/null');
                if ($output) {
                    $lines = explode("\n", $output);
                    foreach ($lines as $line) {
                        if (preg_match('/printer (\S+)/', $line, $matches)) {
                            $printers[] = [
                                'name' => $matches[1],
                                'type' => 'cups'
                            ];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::warning('Could not get available printers: ' . $e->getMessage());
        }

        return $printers;
    }

    /**
     * Utility functions
     */
    private function truncateText(string $text, int $length): string
    {
        return strlen($text) > $length ? substr($text, 0, $length - 3) . '...' : $text;
    }

    private function wrapText(string $text, int $width): string
    {
        return wordwrap($text, $width, "\n", true);
    }
}
