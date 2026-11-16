<x-app-layout title="Farmer dashboard">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header :title="'Welcome back, '.auth()->user()->name" subtitle="Live numbers from your storefront — updated as customers order.">
            <a href="{{ route('farmer.products.create') }}" class="btn-primary">Add product</a>
            <a href="{{ route('farmer.orders.index') }}" class="btn-secondary">View sales</a>
            @if (auth()->user()->slug)
                <a href="{{ route('farmers.show', auth()->user()) }}" class="btn-secondary" target="_blank" rel="noopener">View storefront</a>
            @endif
        </x-page-header>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Total earnings" :value="config('marketplace.currency.symbol').number_format($stats['earnings'], 2)" hint="Excludes cancelled orders" />
            <x-stat-card label="Orders received" :value="number_format($stats['orders'])" hint="Distinct customer orders" />
            <x-stat-card label="Units sold" :value="number_format($stats['units_sold'])" hint="All time" />
            <x-stat-card label="Active products" :value="number_format($stats['active_products'])" :hint="$stats['low_stock'].' low on stock'" />
        </div>

        @if ($stats['low_stock'] > 0)
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <p>
                    {{ $stats['low_stock'] }} {{ \Illuminate\Support\Str::plural('product', $stats['low_stock']) }} at or below 5 units.
                    Restock to keep receiving orders.
                </p>
                <a href="{{ route('farmer.products.index') }}" class="font-semibold underline-offset-4 hover:underline">Manage stock</a>
            </div>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="card overflow-hidden">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Recent sales</h2>
                    <a href="{{ route('farmer.orders.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">All orders</a>
                </div>

                @if ($recentSales->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-slate-500">No sales yet — your first order will appear here.</p>
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach ($recentSales as $sale)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-900">{{ $sale->product_name }}</p>
                                    <p class="text-xs text-slate-500">
                                        Order #{{ $sale->order_id }} &middot; {{ $sale->order?->user?->name }} &middot;
                                        {{ $sale->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-semibold text-slate-900"><x-money :value="$sale->line_total" /></p>
                                    <p class="text-xs text-slate-500">{{ $sale->quantity }} unit{{ $sale->quantity === 1 ? '' : 's' }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="card overflow-hidden">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Top products</h2>
                    <a href="{{ route('farmer.products.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">Manage products</a>
                </div>

                @if ($topProducts->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-slate-500">Add your first product to start selling.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table-base">
                            <thead>
                                <tr>
                                    <th scope="col">Product</th>
                                    <th scope="col" class="text-right">Sold</th>
                                    <th scope="col" class="text-right">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topProducts as $product)
                                    <tr>
                                        <td>
                                            <p class="font-medium text-slate-900">{{ $product->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $product->category?->name }}</p>
                                        </td>
                                        <td class="text-right font-semibold">{{ number_format((int) $product->units_sold) }}</td>
                                        <td class="text-right">
                                            <span @class([
                                                'font-semibold',
                                                'text-rose-600' => $product->stock <= 5,
                                                'text-slate-700' => $product->stock > 5,
                                            ])>{{ $product->stock }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
