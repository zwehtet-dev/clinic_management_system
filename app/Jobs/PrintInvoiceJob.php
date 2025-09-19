<?php
namespace App\Jobs;

use App\Models\Invoice;
use App\Services\PrinterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PrintInvoiceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Invoice $invoice;
    protected array $printerOptions;

    public function __construct(Invoice $invoice, array $printerOptions = [])
    {
        $this->invoice = $invoice;
        $this->printerOptions = $printerOptions;
    }

    public function handle(PrinterService $printerService): void
    {
        try {
            $success = $printerService->printInvoice($this->invoice, $this->printerOptions);

            if ($success) {
                Log::info("Invoice {$this->invoice->invoice_number} printed successfully");
            } else {
                Log::error("Failed to print invoice {$this->invoice->invoice_number}");
            }
        } catch (\Exception $e) {
            Log::error("Print job failed for invoice {$this->invoice->invoice_number}: " . $e->getMessage());
        }
    }
}
