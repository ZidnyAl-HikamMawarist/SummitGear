<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function print($id)
    {
        $rental = Rental::with(['customer', 'details.inventoryItem', 'details.itemUnit', 'payments', 'deposits'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.invoice', compact('rental'));
        
        // Generate nama file
        $fileName = 'Invoice_' . $rental->rental_code . '.pdf';
        
        // Return stream agar PDF terbuka di browser baru
        return $pdf->stream($fileName);
    }
}
