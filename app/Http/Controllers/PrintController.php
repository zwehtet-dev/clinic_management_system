<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Services\PrinterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PrintController extends Controller
{
    protected PrinterService $printerService;

    public function __construct(PrinterService $printerService)
    {
        $this->printerService = $printerService;
    }

    /**
     * Generate print URL for invoice
     */
    public function printInvoice(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $format = $request->get('format', 'receipt'); // receipt, thermal, a4
            $options = ['format' => $format];

            $printUrl = $this->printerService->printInvoice($invoice, $options);

            return response()->json([
                'success' => true,
                'print_url' => $printUrl,
                'format' => $format,
                'message' => 'Print URL generated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Print error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate thermal receipt print URL
     */
    public function printThermal(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $printUrl = $this->printerService->printThermalReceipt($invoice);

            return response()->json([
                'success' => true,
                'print_url' => $printUrl,
                'format' => 'thermal',
                'message' => 'Thermal print URL generated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Thermal print error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate A4 print URL
     */
    public function printA4(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $printUrl = $this->printerService->printA4Invoice($invoice);

            return response()->json([
                'success' => true,
                'print_url' => $printUrl,
                'format' => 'a4',
                'message' => 'A4 print URL generated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'A4 print error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle web-based print requests
     */
    public function webPrint(string $token)
    {
        try {
            $printData = Cache::get("print_token_{$token}");

            if (!$printData) {
                abort(404, 'Print token not found or expired');
            }

            // Load invoice with all necessary relationships
            $invoice = Invoice::with([
                'invoiceItems',
                'invoiceItems.itemable',
                'invoiceable',
                'invoiceable.patient'
            ])->find($printData['invoice_id']);

            if (!$invoice) {
                abort(404, 'Invoice not found');
            }

            // Validate and load relationships safely
            $this->validateAndLoadInvoiceRelationships($invoice);

            // Safely load drug relationships for drug batches
            if ($invoice->invoiceItems) {
                $invoice->invoiceItems->each(function($item) {
                    if ($item->itemable_type === 'App\\Models\\DrugBatch' && $item->itemable) {
                        try {
                            $item->itemable->load('drug');
                        } catch (\Exception $e) {
                            // If drug relationship fails to load, continue without it
                            Log::warning("Failed to load drug for batch {$item->itemable_id}: " . $e->getMessage());
                        }
                    }
                });
            }

            $format = $printData['format'] ?? 'receipt';

            // Clear the token after use
            Cache::forget("print_token_{$token}");

            // Add performance data for large invoices
            $performanceData = $this->getInvoicePerformanceData($invoice, $format);

            // Choose the appropriate view based on format
            $view = match($format) {
                'thermal' => 'print.thermal',
                'a4' => 'print.a4',
                default => 'print.invoice'
            };

            return view($view, compact('invoice', 'format', 'performanceData'));
        } catch (\Exception $e) {
            Log::error('Print error: ' . $e->getMessage(), [
                'token' => $token,
                'invoice_id' => $printData['invoice_id'] ?? 'unknown',
                'format' => $printData['format'] ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            // Return a simple error page instead of aborting
            return response()->view('errors.print-error', [
                'message' => 'Error loading print data: ' . $e->getMessage(),
                'token' => $token,
                'details' => [
                    'invoice_id' => $printData['invoice_id'] ?? 'unknown',
                    'format' => $printData['format'] ?? 'unknown',
                    'error_type' => get_class($e)
                ]
            ], 500);
        }
    }

    /**
     * Validate invoice and load necessary relationships safely
     */
    private function validateAndLoadInvoiceRelationships(Invoice $invoice): void
    {
        // Validate basic invoice data
        if (!$invoice->invoiceable) {
            throw new \Exception('Invoice has no associated record (Visit or DrugSale)');
        }

        // Load relationships based on invoice type
        switch ($invoice->invoiceable_type) {
            case 'App\\Models\\Visit':
                if (!$invoice->invoiceable->relationLoaded('doctor')) {
                    try {
                        $invoice->invoiceable->load('doctor');
                    } catch (\Exception $e) {
                        Log::warning("Could not load doctor for visit: " . $e->getMessage());
                    }
                }
                break;
                
            case 'App\\Models\\DrugSale':
                // DrugSale doesn't have doctor relationship, so we don't try to load it
                break;
                
            default:
                Log::warning("Unknown invoiceable type: {$invoice->invoiceable_type}");
        }

        // Load patient relationship if not already loaded
        if (!$invoice->invoiceable->relationLoaded('patient')) {
            try {
                $invoice->invoiceable->load('patient');
            } catch (\Exception $e) {
                Log::warning("Could not load patient: " . $e->getMessage());
            }
        }
    }

    /**
     * Get performance optimization data for large invoices
     */
    private function getInvoicePerformanceData(Invoice $invoice, string $format): array
    {
        $itemCount = $invoice->invoiceItems->count();
        $drugCount = $invoice->invoiceItems->where('itemable_type', 'App\\Models\\DrugBatch')->count();
        $serviceCount = $invoice->invoiceItems->where('itemable_type', 'App\\Models\\Service')->count();

        $limits = match($format) {
            'thermal' => \App\Models\Setting::get('thermal_max_items', 12),
            'receipt' => \App\Models\Setting::get('receipt_max_items', 15),
            'a4' => 30, // Items per page for A4
            default => 15
        };

        return [
            'total_items' => $itemCount,
            'drug_count' => $drugCount,
            'service_count' => $serviceCount,
            'display_limit' => $limits,
            'will_paginate' => $itemCount > $limits,
            'compact_items' => max(0, $itemCount - $limits),
            'estimated_pages' => $format === 'a4' ? ceil($itemCount / 30) : 1,
        ];
    }

}

