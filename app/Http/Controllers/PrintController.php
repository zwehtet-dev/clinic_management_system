<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\PrinterService;
use App\Jobs\PrintInvoiceJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Filament\Notifications\Notification;

class PrintController extends Controller
{
    protected PrinterService $printerService;

    public function __construct(PrinterService $printerService)
    {
        $this->printerService = $printerService;
    }

    /**
     * Print invoice immediately
     */
    public function printInvoice(Request $request, Invoice $invoice): JsonResponse
    {
        try {
            $printerOptions = [
                'printer_type' => $request->get('printer_type', config('printing.default_printer_type')),
                'printer_name' => $request->get('printer_name'),
                'copies' => $request->get('copies', config('printing.print_copies')),
            ];

            if (config('printing.enable_print_queue')) {
                // Queue the print job
                PrintInvoiceJob::dispatch($invoice, $printerOptions);

                return response()->json([
                    'success' => true,
                    'message' => 'Invoice queued for printing',
                ]);
            } else {
                // Print immediately
                $success = $this->printerService->printInvoice($invoice, $printerOptions);

                return response()->json([
                    'success' => $success,
                    'message' => $success ? 'Invoice printed successfully' : 'Failed to print invoice',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Print error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test printer connection
     */
    public function testPrinter(Request $request): JsonResponse
    {
        try {
            $printerOptions = [
                'printer_type' => $request->get('printer_type', config('printing.default_printer_type')),
                'printer_name' => $request->get('printer_name'),
                'connection_type' => $request->get('connection_type'),
                'host' => $request->get('host'),
                'port' => $request->get('port'),
            ];

            $success = $this->printerService->testPrinter($printerOptions);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Printer test successful' : 'Printer test failed',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available printers
     */
    public function getAvailablePrinters(): JsonResponse
    {
        try {
            $printers = $this->printerService->getAvailablePrinters();

            return response()->json([
                'success' => true,
                'printers' => $printers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting printers: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print multiple invoices
     */
    public function printMultipleInvoices(Request $request): JsonResponse
    {
        try {
            $invoiceIds = $request->get('invoice_ids', []);
            $printerOptions = [
                'printer_type' => $request->get('printer_type', config('printing.default_printer_type')),
                'printer_name' => $request->get('printer_name'),
            ];

            $printed = 0;
            foreach ($invoiceIds as $invoiceId) {
                $invoice = Invoice::find($invoiceId);
                if ($invoice) {
                    if (config('printing.enable_print_queue')) {
                        PrintInvoiceJob::dispatch($invoice, $printerOptions);
                        $printed++;
                    } else {
                        if ($this->printerService->printInvoice($invoice, $printerOptions)) {
                            $printed++;
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Queued {$printed} invoices for printing",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk print error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
