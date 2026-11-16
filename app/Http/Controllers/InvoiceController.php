<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\QrCodeImage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function show(Request $request, Order $order): Response
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $order->user_id === $user->id, 403);

        $order->load(['items.farmer:id,name', 'user:id,name,email,phone']);

        $pdf = Pdf::loadView('orders.invoice', [
            'order' => $order,
            'qr' => QrCodeImage::dataUri(route('orders.show', $order), 5),
            'issuedAt' => now(),
        ])->setPaper('a4');

        return $pdf->download("invoice-{$order->id}.pdf");
    }
}
