<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        @page { margin: 38px 42px; }
        * { box-sizing: border-box; }
        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #2E2A21;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
        }
        .header { width: 100%; border-bottom: 2px solid #3B5232; padding-bottom: 18px; }
        .header td { vertical-align: top; }
        .brand { font-size: 20px; font-weight: bold; color: #3B5232; letter-spacing: -0.3px; }
        .brand small { display: block; font-size: 10px; font-weight: normal; color: #6B6152; letter-spacing: 0.6px; text-transform: uppercase; }
        .doc-title { text-align: right; font-size: 24px; font-weight: bold; letter-spacing: 1px; color: #2E2A21; margin: 0; }
        .muted { color: #6B6152; }
        .small { font-size: 10px; }
        .meta { margin-top: 22px; width: 100%; }
        .meta td { vertical-align: top; width: 50%; }
        .panel { background: #F7F1E4; border: 1px solid #DCD0BC; border-radius: 4px; padding: 12px 14px; }
        .panel h3 { margin: 0 0 6px; font-size: 10px; letter-spacing: 0.8px; text-transform: uppercase; color: #6B6152; }
        .panel p { margin: 0; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 26px; }
        table.items th {
            text-align: left;
            font-size: 9px;
            letter-spacing: 0.7px;
            text-transform: uppercase;
            color: #6B6152;
            border-bottom: 1px solid #C4B69E;
            padding: 8px 6px;
        }
        table.items td { padding: 9px 6px; border-bottom: 1px solid #DCD0BC; vertical-align: top; }
        .right { text-align: right; }
        .center { text-align: center; }
        .totals { width: 46%; margin-left: 54%; margin-top: 14px; border-collapse: collapse; }
        .totals td { padding: 6px 6px; }
        .totals .grand td { border-top: 2px solid #3B5232; font-size: 14px; font-weight: bold; color: #3B5232; padding-top: 9px; }
        .footer { margin-top: 34px; border-top: 1px solid #DCD0BC; padding-top: 16px; width: 100%; }
        .footer td { vertical-align: top; }
        .qr { text-align: right; }
        .qr img { width: 96px; height: 96px; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <div class="brand">Farmers Market</div>
                <div class="small muted">Direct from local farms &middot; Dhaka, Bangladesh</div>
            </td>
            <td>
                <p class="doc-title">INVOICE</p>
                <p class="small muted" style="text-align: right; margin: 4px 0 0;">
                    Invoice FM-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}<br>
                    Issued {{ $issuedAt->format('d M Y') }}
                </p>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td style="padding-right: 12px;">
                <div class="panel">
                    <h3>Billed to</h3>
                    <p><strong>{{ $order->user->name }}</strong></p>
                    <p class="muted">{{ $order->user->email }}</p>
                    <p class="muted">{{ $order->delivery_phone }}</p>
                </div>
            </td>
            <td style="padding-left: 12px;">
                <div class="panel">
                    <h3>Deliver to</h3>
                    <p><strong>{{ $order->delivery_name }}</strong></p>
                    <p class="muted">{{ $order->delivery_address }}</p>
                    @if ($order->notes)
                        <p class="muted small">Note: {{ $order->notes }}</p>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th>Sold by</th>
                <th class="center">Qty</th>
                <th class="right">Unit price</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="muted">{{ $item->farmer?->name }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">{{ number_format((float) $item->unit_price, 2) }} BDT</td>
                    <td class="right"><strong>{{ number_format((float) $item->line_total, 2) }} BDT</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="muted">Subtotal</td>
            <td class="right">{{ number_format((float) $order->subtotal, 2) }} BDT</td>
        </tr>
        @if ((float) $order->discount > 0)
            <tr>
                <td class="muted">Discount @if ($order->coupon_code) ({{ $order->coupon_code }}) @endif</td>
                <td class="right" style="color: #3B5232;">−{{ number_format((float) $order->discount, 2) }} BDT</td>
            </tr>
        @endif
        <tr>
            <td class="muted">Platform fee ({{ (int) (config('marketplace.platform_fee') * 100) }}%)</td>
            <td class="right">{{ number_format((float) $order->platform_fee, 2) }} BDT</td>
        </tr>
        <tr class="grand">
            <td>Total</td>
            <td class="right">{{ number_format((float) $order->total, 2) }} BDT</td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 22px;">
        <tr>
            <td>
                <span class="small muted">Placed {{ $order->placed_at->format('d M Y, h:i A') }}</span><br>
                <span class="badge" style="margin-top: 6px; background: #E7EDDF; color: #3B5232;">
                    {{ $order->status->label() }}
                </span>
            </td>
            <td class="right small muted">
                Payment method: {{ $order->payment_method->label() }}<br>
                Thank you for buying directly from farmers.
            </td>
        </tr>
    </table>

    <table class="footer">
        <tr>
            <td>
                <p style="margin: 0; font-weight: bold;">Need help?</p>
                <p class="muted small" style="margin: 2px 0 0;">
                    Quote invoice FM-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }} when contacting support.<br>
                    {{ config('app.name') }} &middot; {{ config('app.url') }}
                </p>
            </td>
            <td class="qr">
                <img src="{{ $qr }}" alt="QR code to view order {{ $order->id }}">
                <p class="small muted" style="margin: 4px 0 0;">Scan to view this order</p>
            </td>
        </tr>
    </table>
</body>
</html>
