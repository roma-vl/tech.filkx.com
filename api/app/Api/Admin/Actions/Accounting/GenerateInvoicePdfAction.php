<?php

namespace App\Api\Admin\Actions\Accounting;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfInstance;

class GenerateInvoicePdfAction
{
    public function execute(Order $order): PdfInstance
    {
        return Pdf::loadView('pdf.invoice', ['order' => $order->loadMissing('items')]);
    }
}
