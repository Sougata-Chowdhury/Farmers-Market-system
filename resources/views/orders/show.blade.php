<x-app-layout :title="'Order #'.$order->id">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('orders.index') }}" class="text-sm text-slate-500 hover:text-emerald-700">&larr; Back to orders</a>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="font-display text-3xl font-medium tracking-tight text-slate-900">Order #{{ $order->id }}</h1>
                    <x-status-badge :status="$order->status" />
                </div>
                <p class="mt-1 text-sm text-slate-500">Placed {{ $order->placed_at->format('d M Y, h:i A') }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('orders.invoice', $order) }}" class="btn-secondary">Download invoice</a>

                @can('cancel', $order)
                    <form method="POST" action="{{ route('orders.cancel', $order) }}"
                          onsubmit="return confirm('Cancel this order and release the stock?');">
                        @csrf
                        <button type="submit" class="btn-danger">Cancel order</button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.5fr_0.5fr] lg:items-start">
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th scope="col">Product</th>
                                <th scope="col">Farmer</th>
                                <th scope="col" class="text-right">Unit price</th>
                                <th scope="col" class="text-center">Qty</th>
                                <th scope="col" class="text-right">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="font-medium text-slate-900">{{ $item->product_name }}</td>
                                    <td>{{ $item->farmer?->name }}</td>
                                    <td class="text-right"><x-money :value="$item->unit_price" /></td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right font-semibold"><x-money :value="$item->line_total" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-right text-sm text-slate-500">Subtotal</td>
                                <td class="px-4 py-2 text-right text-sm font-medium"><x-money :value="$order->subtotal" /></td>
                            </tr>
                            @if ((float) $order->discount > 0)
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right text-sm text-emerald-700">
                                        Discount @if ($order->coupon_code) ({{ $order->coupon_code }}) @endif
                                    </td>
                                    <td class="px-4 py-2 text-right text-sm font-semibold text-emerald-700">−<x-money :value="$order->discount" /></td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-right text-sm text-slate-500">
                                    Platform fee ({{ (int) (config('marketplace.platform_fee') * 100) }}%)
                                </td>
                                <td class="px-4 py-2 text-right text-sm font-medium"><x-money :value="$order->platform_fee" /></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-right text-base font-semibold text-slate-900">Total</td>
                                <td class="px-4 py-3 text-right text-base font-bold text-slate-900"><x-money :value="$order->total" /></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <aside class="space-y-4">
                <div class="card p-5">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Order timeline</h2>
                    <div class="mt-4">
                        <x-order-timeline :order="$order" />
                    </div>
                </div>

                <div class="card p-5">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Delivery</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Recipient</dt>
                            <dd class="font-medium text-slate-900">{{ $order->delivery_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Mobile</dt>
                            <dd class="text-slate-700">{{ $order->delivery_phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Address</dt>
                            <dd class="text-slate-700">{{ $order->delivery_address }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Payment</dt>
                            <dd class="text-slate-700">{{ $order->payment_method->label() }}</dd>
                        </div>
                        @if ($order->notes)
                            <div>
                                <dt class="text-xs uppercase tracking-wider text-slate-400">Note</dt>
                                <dd class="text-slate-700">{{ $order->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="card p-5 text-sm text-slate-500">
                    Need help with this order? Contact support and quote <span class="font-semibold text-slate-700">#{{ $order->id }}</span>.
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
