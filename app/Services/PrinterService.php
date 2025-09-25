<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Exception;

class PrinterService
{
    /**
     * Generate print URL for web-based printing
     */
    public function generatePrintUrl(Invoice $invoice, string $format = 'receipt'): string
    {
        try {
            // Validate invoice
            if (!$invoice || !$invoice->id) {
                throw new Exception('Invalid invoice provided');
            }

            // Validate format
            $validFormats = ['receipt', 'thermal', 'a4'];
            if (!in_array($format, $validFormats)) {
                $format = 'receipt'; // Default fallback
            }

            // Generate a secure token for printing
            $printToken = \Str::random(32);
            
            // Store print data temporarily (5 minutes)
            \Cache::put("print_token_{$printToken}", [
                'invoice_id' => $invoice->id,
                'format' => $format,
                'created_at' => now(),
                'user_id' => auth()->id() ?? null,
            ], 300);

            return url("/print/{$printToken}");
        } catch (Exception $e) {
            Log::error('Error generating print URL: ' . $e->getMessage());
            throw new Exception('Failed to generate print URL: ' . $e->getMessage());
        }
    }

    /**
     * Print invoice (web-based for cloud hosting)
     */
    public function printInvoice(Invoice $invoice, array $options = []): string
    {
        try {
            $format = $options['format'] ?? \App\Models\Setting::get('print_format', 'receipt');
            return $this->generatePrintUrl($invoice, $format);
        } catch (Exception $e) {
            Log::error('Print error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate thermal receipt print URL
     */
    public function printThermalReceipt(Invoice $invoice): string
    {
        return $this->generatePrintUrl($invoice, 'thermal');
    }

    /**
     * Generate A4 print URL
     */
    public function printA4Invoice(Invoice $invoice): string
    {
        return $this->generatePrintUrl($invoice, 'a4');
    }

    /**
     * Test printer (for web-based printing, just return success)
     */
    public function testPrinter(array $options = []): bool
    {
        // For web-based printing, we can't really test the physical printer
        // Just return true to indicate the system is ready
        return true;
    }
}
