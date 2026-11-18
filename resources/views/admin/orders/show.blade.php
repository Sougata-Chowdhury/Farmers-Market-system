<x-app-layout :title="'Order #'.$order->id">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft transition hover:text-clay-600">&larr; Back to orders</a>

        <p class="eyebrow mt-5">Order record</p>
        <div class="mt-1 flex flex-wrap items-end justify-between gap-4 border-b border-line-dark pb-5">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-display text-3xl font-medium tracking-tight text-ink">Order #{{ $order->id }}</h1>
                <x-status-badge :status="$order->status" />
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">Placed {{ $order->placed_at->format('d M Y, h:i A') }}</p>
                <a href="{{ route('orders.invoice', $order) }}" class="btn-secondary">Invoice PDF</a>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.5fr_0.5fr] lg:items-start">
            <div class="space-y-4">
                <div class="card overflow-hidden">
                    <div class="card-head">
                        <div>
                            <p class="eyebrow">What was ordered</p>
                            <h2 class="mt-1 font-display text-xl font-semibold text-ink">Line items</h2>
                        </div>
                        <span class="tag">{{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item', $order->items->count()) }}</span>
                    </div>

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
                                        <td class="font-medium text-ink">{{ $item->product_name }}</td>
                                        <td class="text-ink-soft">{{ $item->farmer?->name }}</td>
                                        <td class="text-right"><x-money :value="$item->unit_price" /></td>
                                        <td class="text-center font-mono text-[13px] text-ink-soft">{{ $item->quantity }}</td>
                                        <td class="text-right font-semibold"><x-money :value="$item->line_total" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-paper-deep/60">
                                <tr class="border-t border-line">
                                    <td colspan="4" class="px-4 py-2 text-right font-mono text-[10px] uppercase tracking-label text-ink-soft">Subtotal</td>
                                    <td class="px-4 py-2 text-right text-sm font-medium text-ink"><x-money :value="$order->subtotal" /></td>
                                </tr>
                                @if ((float) $order->discount > 0)
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 text-right font-mono text-[10px] uppercase tracking-label text-forest-700">
                                            Discount @if ($order->coupon_code) ({{ $order->coupon_code }}) @endif
                                        </td>
                                        <td class="px-4 py-2 text-right text-sm font-semibold text-forest-700">−<x-money :value="$order->discount" /></td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="4" class="px-4 py-2 text-right font-mono text-[10px] uppercase tracking-label text-ink-soft">Platform fee</td>
                                    <td class="px-4 py-2 text-right text-sm font-medium text-ink"><x-money :value="$order->platform_fee" /></td>
                                </tr>
                                <tr class="border-t-2 border-line-dark">
                                    <td colspan="4" class="px-4 py-3 text-right font-mono text-[10px] font-bold uppercase tracking-label text-ink">Total</td>
                                    <td class="px-4 py-3 text-right font-display text-base font-bold text-ink"><x-money :value="$order->total" /></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="card-head">
                        <div>
                            <p class="eyebrow">Delivery</p>
                            <h2 class="mt-1 font-display text-xl font-semibold text-ink">Fulfilment</h2>
                        </div>
                    </div>
                    <dl class="grid gap-4 p-5 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-faint">Recipient</dt>
                            <dd class="mt-1 font-medium text-ink">{{ $order->delivery_name }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-faint">Mobile</dt>
                            <dd class="mt-1 text-ink-soft">{{ $order->delivery_phone }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-faint">Address</dt>
                            <dd class="mt-1 text-ink-soft">{{ $order->delivery_address }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-faint">Payment method</dt>
                            <dd class="mt-1 text-ink-soft">{{ $order->payment_method->label() }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-faint">Saved address</dt>
                            <dd class="mt-1 text-ink-soft">{{ $order->address?->label ?? 'Entered at checkout' }}</dd>
                        </div>
                        @if ($order->notes)
                            <div class="sm:col-span-2">
                                <dt class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-faint">Customer note</dt>
                                <dd class="mt-1 border-l-2 border-clay-300 pl-3 text-ink-soft">{{ $order->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <aside class="space-y-4">
                <div class="card overflow-hidden">
                    <div class="card-head">
                        <div>
                            <p class="eyebrow">Next step</p>
                            <h2 class="mt-1 font-display text-xl font-semibold text-ink">Status</h2>
                        </div>
                    </div>

                    <div class="p-5">
                        @php($transitions = $order->status->allowedTransitions())

                        @if ($transitions === [])
                            <p class="text-sm text-ink-soft">
                                This order is {{ strtolower($order->status->label()) }} and can no longer change.
                                @if ($order->status === \App\Enums\OrderStatus::Cancelled)
                                    Stock was returned to the farmers.
                                @endif
                            </p>
                        @else
                            <p class="text-sm text-ink-soft">Move this order forward as fulfilment progresses.</p>
                            <div class="mt-4 space-y-2">
                                @foreach ($transitions as $target)
                                    <form method="POST" action="{{ route('admin.orders.status', $order) }}"
                                          @if ($target === \App\Enums\OrderStatus::Cancelled) onsubmit="return confirm('Cancel this order and restore stock?');" @endif>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $target->value }}">
                                        <button type="submit" @class([
                                            'w-full',
                                            'btn-danger' => $target === \App\Enums\OrderStatus::Cancelled,
                                            'btn-primary' => $target !== \App\Enums\OrderStatus::Cancelled,
                                        ])>
                                            {{ $target === \App\Enums\OrderStatus::Cancelled ? 'Cancel order' : 'Mark as '.$target->label() }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="card-head">
                        <div>
                            <p class="eyebrow">The paper trail</p>
                            <h2 class="mt-1 font-display text-xl font-semibold text-ink">Order timeline</h2>
                        </div>
                    </div>
                    <div class="p-5">
                        <x-order-timeline :order="$order" />
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="card-head">
                        <div>
                            <p class="eyebrow">On the order</p>
                            <h2 class="mt-1 font-display text-xl font-semibold text-ink">Customer</h2>
                        </div>
                    </div>
                    <dl class="space-y-3 p-5 text-sm">
                        <div>
                            <dt class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-faint">Name</dt>
                            <dd class="mt-1 font-medium text-ink">{{ $order->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-faint">Email</dt>
                            <dd class="mt-1 text-ink-soft">{{ $order->user->email }}</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-faint">Phone</dt>
                            <dd class="mt-1 text-ink-soft">{{ $order->user->phone }}</dd>
                        </div>
                    </dl>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
