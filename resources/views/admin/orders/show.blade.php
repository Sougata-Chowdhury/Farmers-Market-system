<x-app-layout :title="'Order #'.$order->id">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-slate-500 hover:text-emerald-700">&larr; Back to orders</a>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-display text-3xl font-medium tracking-tight text-slate-900">Order #{{ $order->id }}</h1>
                <x-status-badge :status="$order->status" />
            </div>
            <div class="flex items-center gap-3">
                <p class="text-sm text-slate-500">Placed {{ $order->placed_at->format('d M Y, h:i A') }}</p>
                <a href="{{ route('orders.invoice', $order) }}" class="btn-secondary">Invoice PDF</a>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.5fr_0.5fr] lg:items-start">
            <div class="space-y-4">
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
                                    <td colspan="4" class="px-4 py-2 text-right text-sm text-slate-500">Platform fee</td>
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

                <div class="card p-5">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Fulfilment</h2>
                    <dl class="mt-3 grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Recipient</dt>
                            <dd class="font-medium text-slate-900">{{ $order->delivery_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Mobile</dt>
                            <dd class="text-slate-700">{{ $order->delivery_phone }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Address</dt>
                            <dd class="text-slate-700">{{ $order->delivery_address }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Payment method</dt>
                            <dd class="text-slate-700">{{ $order->payment_method->label() }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Saved address</dt>
                            <dd class="text-slate-700">{{ $order->address?->label ?? 'Entered at checkout' }}</dd>
                        </div>
                        @if ($order->notes)
                            <div class="sm:col-span-2">
                                <dt class="text-xs uppercase tracking-wider text-slate-400">Customer note</dt>
                                <dd class="text-slate-700">{{ $order->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <aside class="space-y-4">
                <div class="card p-5">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Status</h2>

                    @php($transitions = $order->status->allowedTransitions())

                    @if ($transitions === [])
                        <p class="mt-3 text-sm text-slate-500">
                            This order is {{ strtolower($order->status->label()) }} and can no longer change.
                            @if ($order->status === \App\Enums\OrderStatus::Cancelled)
                                Stock was returned to the farmers.
                            @endif
                        </p>
                    @else
                        <p class="mt-1 text-sm text-slate-500">Move this order forward as fulfilment progresses.</p>
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

                <div class="card p-5">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Order timeline</h2>
                    <div class="mt-4">
                        <x-order-timeline :order="$order" />
                    </div>
                </div>

                <div class="card p-5">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Customer</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Name</dt>
                            <dd class="font-medium text-slate-900">{{ $order->user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Email</dt>
                            <dd class="text-slate-700">{{ $order->user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Phone</dt>
                            <dd class="text-slate-700">{{ $order->user->phone }}</dd>
                        </div>
                    </dl>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
