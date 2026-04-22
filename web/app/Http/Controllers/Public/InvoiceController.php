<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function download(Request $request, Invoice $invoice): Response
    {
        // Sécurité : un abonné ne télécharge que ses propres factures ; BO (com/adm/sup) voit tout.
        $user = $request->user();
        $order = $invoice->order;

        $isOwner = $user && $order && $order->user_id === $user->id;
        $isBo = $user && $user->hasAnyRole(['com', 'adm', 'sup']);
        abort_unless($isOwner || $isBo, 403);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice->load(['order.plan', 'order.user']),
            'publisher' => config('gm.publisher'),
        ])->setPaper('a4');

        return $pdf->download($invoice->number.'.pdf');
    }
}
