<x-app-layout title="Sales">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Sales" subtitle="Every order that includes your products.">
            <a href="{{ route('farmer.exports.orders') }}" class="btn-secondary">Export CSV</a>
            <a href="{{ route('farmer.products.index') }}" class="btn-secondary">Manage products</a>
        </x-page-header>

        @if ($orders->isEmpty())
            <div class="mt-6">
                <x-empty-state title="No sales yet" description="Once a customer orders one of your products, the order will appear here.">
                    <a href="{{ route('farmer.products.index') }}" class="btn-primary">Review my listings</a>
                </x-empty-state>
            </div>
        @else
            <div class="mt-6 space-y-4">
                @foreach ($orders as $order)
                    @php($subtotal = $order->items->sum('line_total'))
                    <article class="card overflow-hidden">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <p class="font-semibold text-slate-900">Order #{{ $order->id }}</p>
                                <x-status-badge :status="$order->status" />
                                <span class="text-sm text-slate-500">
                                    {{ $order->placed_at->format('d M Y, h:i A') }} &middot; {{ $order->user?->name }}
                                </span>
                            </div>
                            <p class="text-sm font-semibold text-slate-900">
                                Your share: <x-money :value="$subtotal" />
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="table-base">
                                <thead>
                                    <tr>
                                        <th scope="col">Product</th>
                                        <th scope="col" class="text-right">Unit price</th>
                                        <th scope="col" class="text-center">Qty</th>
                                        <th scope="col" class="text-right">Line total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td class="font-medium text-slate-900">{{ $item->product_name }}</td>
                                            <td class="text-right"><x-money :value="$item->unit_price" /></td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-right font-semibold"><x-money :value="$item->line_total" /></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">{{ $orders->links() }}</div>
        @endif
    </div>
</x-app-layout>
