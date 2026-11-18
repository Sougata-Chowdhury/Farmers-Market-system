<x-app-layout title="Farmer dashboard">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <p class="eyebrow mb-2">The stall ledger</p>
        <x-page-header :title="'Welcome back, '.auth()->user()->name" subtitle="Live numbers from your storefront — updated as customers order.">
            <a href="{{ route('farmer.exports.orders') }}" class="btn-secondary" data-csv-export data-filename="my-sales.csv" download>Export sales CSV</a>
            <a href="{{ route('farmer.products.create') }}" class="btn-primary">Add product</a>
        </x-page-header>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Total earnings" :value="config('marketplace.currency.symbol').number_format($stats['earnings'], 2)" hint="Excludes cancelled orders" />
            <x-stat-card label="Last 30 days" :value="config('marketplace.currency.symbol').number_format($lastThirtyDays, 2)" hint="Your recent momentum" />
            <x-stat-card label="Orders received" :value="number_format($stats['orders'])" hint="Distinct customer orders" />
            <x-stat-card label="Units sold" :value="number_format($stats['units_sold'])" :hint="$stats['active_products'].' active products'" />
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.5fr_0.5fr] lg:items-start">
            <section class="card p-5 sm:p-6">
                <p class="eyebrow">Daily takings</p>
                <h2 class="mt-1 font-display text-2xl font-semibold text-ink">Sales, last 30 days</h2>
                <p class="mt-1 text-sm text-ink-soft">Your share of each order, before platform fees.</p>
                <div class="mt-4">
                    <x-line-chart :points="$salesSeries" label="Daily sales" :height="190" />
                </div>
            </section>

            <section class="card p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="eyebrow">Low stock</p>
                        <h2 class="mt-1 font-display text-2xl font-semibold text-ink">Stock watch</h2>
                    </div>
                    <span @class([
                        'rounded-sm px-2 py-1 font-mono text-[9px] font-bold uppercase tracking-label ring-1 ring-inset',
                        'bg-ochre-100 text-ochre-800 ring-ochre-200' => $stats['low_stock'] > 0,
                        'bg-forest-100 text-forest-800 ring-forest-200' => $stats['low_stock'] === 0,
                    ])>
                        {{ $stats['low_stock'] }} {{ \Illuminate\Support\Str::plural('product', $stats['low_stock']) }}
                    </span>
                </div>

                @if ($lowStockProducts->isEmpty())
                    <p class="hand mt-4 border border-dashed border-line-dark bg-paper-light px-4 py-3 text-xl text-forest-800">
                        Everything is comfortably stocked. Keep it up.
                    </p>
                @else
                    <ul class="mt-4 divide-y divide-dashed divide-line-dark/60">
                        @foreach ($lowStockProducts as $product)
                            <li class="flex items-center justify-between gap-3 py-2.5">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $product->name }}</p>
                                    <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">{{ $product->stock }} {{ $product->unit }} left</p>
                                </div>
                                <a href="{{ route('farmer.products.edit', $product) }}"
                                   class="shrink-0 font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 hover:underline">Restock</a>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('farmer.products.index') }}" class="mt-3 inline-block font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft hover:text-ink">
                        Manage all produce &rarr;
                    </a>
                @endif
            </section>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="card overflow-hidden">
                <div class="card-head">
                    <div>
                        <p class="eyebrow">Fresh off the ledger</p>
                        <h2 class="mt-1 font-display text-xl font-semibold text-ink">Recent sales</h2>
                    </div>
                    <a href="{{ route('farmer.orders.index') }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 hover:underline">All orders</a>
                </div>

                @if ($recentSales->isEmpty())
                    <p class="hand px-5 py-10 text-center text-xl text-ink-faint">No sales yet — your first order will appear here.</p>
                @else
                    <ul class="divide-y divide-dashed divide-line-dark/60">
                        @foreach ($recentSales as $sale)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $sale->product_name }}</p>
                                    <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">
                                        Order #{{ $sale->order_id }} &middot; {{ $sale->order?->user?->name }} &middot;
                                        {{ $sale->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="font-display text-base font-semibold text-ink"><x-money :value="$sale->line_total" /></p>
                                    <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">{{ $sale->quantity }} unit{{ $sale->quantity === 1 ? '' : 's' }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="card overflow-hidden">
                <div class="card-head">
                    <div>
                        <p class="eyebrow">Your best rows</p>
                        <h2 class="mt-1 font-display text-xl font-semibold text-ink">Best sellers</h2>
                    </div>
                    <a href="{{ route('farmer.exports.products') }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 hover:underline" data-csv-export data-filename="my-products.csv" download>Export CSV</a>
                </div>

                @if ($topProducts->isEmpty())
                    <p class="hand px-5 py-10 text-center text-xl text-ink-faint">Add your first product to start selling.</p>
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
                                            <p class="font-medium text-ink">{{ $product->name }}</p>
                                            <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">{{ $product->category?->name }}</p>
                                        </td>
                                        <td class="text-right font-semibold">{{ number_format((int) $product->units_sold) }}</td>
                                        <td class="text-right">
                                            <span @class([
                                                'font-semibold',
                                                'text-clay-600' => $product->stock <= 5,
                                                'text-ink' => $product->stock > 5,
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
