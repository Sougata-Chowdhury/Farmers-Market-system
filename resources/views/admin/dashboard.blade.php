<x-app-layout title="Admin dashboard">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <p class="eyebrow mb-2">The market office</p>
        <x-page-header title="Platform overview" subtitle="Revenue, orders and marketplace health at a glance.">
            <a href="{{ route('admin.exports.orders') }}" class="btn-secondary" data-csv-export data-filename="orders.csv" download>Export orders CSV</a>
            <a href="{{ route('admin.orders.index') }}" class="btn-primary">All orders</a>
        </x-page-header>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Order revenue" :value="config('marketplace.currency.symbol').number_format($stats['revenue'], 2)" hint="Excludes cancelled orders" />
            <x-stat-card label="Platform fees" :value="config('marketplace.currency.symbol').number_format($stats['fees'], 2)" hint="Retained by the platform" />
            <x-stat-card label="Orders" :value="number_format($stats['orders'])" :hint="$stats['pending'].' awaiting confirmation'" />
            <x-stat-card label="Average order" :value="config('marketplace.currency.symbol').number_format($stats['average_order'], 2)" hint="Across completed orders" />
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card flex items-center justify-between p-5">
                <div>
                    <p class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft">Customers</p>
                    <p class="mt-1 font-display text-2xl font-semibold text-ink">{{ number_format($stats['customers']) }}</p>
                </div>
                <a href="{{ route('admin.customers.index') }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 hover:underline">View</a>
            </div>
            <div class="card flex items-center justify-between p-5">
                <div>
                    <p class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft">Farmers</p>
                    <p class="mt-1 font-display text-2xl font-semibold text-ink">{{ number_format($stats['farmers']) }}</p>
                </div>
                <a href="{{ route('admin.farmers.index') }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 hover:underline">View</a>
            </div>
            <div class="card flex items-center justify-between p-5">
                <div>
                    <p class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft">Products</p>
                    <p class="mt-1 font-display text-2xl font-semibold text-ink">{{ number_format($stats['products']) }}</p>
                </div>
                <a href="{{ route('admin.products.index') }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 hover:underline">Moderate</a>
            </div>
            <div class="card flex items-center justify-between p-5">
                <div>
                    <p class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft">Pending orders</p>
                    <p class="mt-1 font-display text-2xl font-semibold text-ochre-600">{{ number_format($stats['pending']) }}</p>
                </div>
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 hover:underline">Review</a>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.5fr_0.5fr] lg:items-start">
            <section class="card p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="eyebrow">The books</p>
                        <h2 class="mt-1 font-display text-2xl font-semibold text-ink">Revenue, last 30 days</h2>
                        <p class="mt-1 text-sm text-ink-soft">Cancelled orders are excluded from the totals.</p>
                    </div>
                </div>
                <div class="mt-4">
                    <x-line-chart :points="$revenueSeries" label="Daily revenue" :height="190" />
                </div>
            </section>

            <section class="card p-5 sm:p-6">
                <p class="eyebrow">How the week went</p>
                <h2 class="mt-1 font-display text-2xl font-semibold text-ink">Orders by status</h2>
                <p class="mt-1 text-sm text-ink-soft">Every order ever placed.</p>
                <div class="mt-4">
                    <x-donut-chart :segments="$statusSegments" title="Orders by status" unit="orders" />
                </div>
            </section>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_0.6fr] lg:items-start">
            <section class="card overflow-hidden">
                <div class="card-head">
                    <div>
                        <p class="eyebrow">Just through the door</p>
                        <h2 class="mt-1 font-display text-xl font-semibold text-ink">Recent orders</h2>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 hover:underline">All orders</a>
                </div>

                @if ($recentOrders->isEmpty())
                    <p class="hand px-5 py-10 text-center text-xl text-ink-faint">No orders have been placed yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table-base">
                            <thead>
                                <tr>
                                    <th scope="col">Order</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Placed</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs font-bold text-forest-700 underline decoration-forest-600/30 decoration-2 underline-offset-4 hover:text-forest-900">
                                                #{{ $order->id }}
                                            </a>
                                        </td>
                                        <td>{{ $order->user?->name }}</td>
                                        <td class="font-mono text-[11px] text-ink-soft">{{ $order->placed_at->format('d M, H:i') }}</td>
                                        <td><x-status-badge :status="$order->status" /></td>
                                        <td class="text-right font-semibold"><x-money :value="$order->total" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <div class="space-y-4">
                <section class="card p-5">
                    <p class="eyebrow">What moves</p>
                    <h2 class="mt-1 font-display text-xl font-semibold text-ink">Top categories</h2>
                    @if ($topCategories->isEmpty())
                        <p class="mt-3 text-sm text-ink-soft">No sales yet.</p>
                    @else
                        @php($categoryMax = max(1, (float) $topCategories->max('revenue')))
                        <ul class="mt-4 space-y-3">
                            @foreach ($topCategories as $category)
                                <li>
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="font-medium text-ink">{{ $category->name }}</span>
                                        <span class="font-mono font-bold text-ink"><x-money :value="$category->revenue" /></span>
                                    </div>
                                    <div class="mt-1.5 h-2 overflow-hidden bg-paper-deep">
                                        <div class="h-full bg-forest-600" style="width: {{ round(((float) $category->revenue / $categoryMax) * 100) }}%"></div>
                                    </div>
                                    <p class="mt-1 font-mono text-[9px] uppercase tracking-label text-ink-faint">{{ number_format((int) $category->units) }} units sold</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="card p-5">
                    <p class="eyebrow">The popular shelf</p>
                    <h2 class="mt-1 font-display text-xl font-semibold text-ink">Best sellers</h2>
                    @if ($topProducts->isEmpty())
                        <p class="mt-3 text-sm text-ink-soft">No sales yet.</p>
                    @else
                        <ul class="mt-3 divide-y divide-dashed divide-line-dark/60">
                            @foreach ($topProducts as $product)
                                <li class="flex items-center justify-between gap-3 py-2 text-sm">
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-ink">{{ $product->product_name }}</p>
                                        <p class="font-mono text-[9px] uppercase tracking-label text-ink-faint">{{ number_format((int) $product->units) }} units</p>
                                    </div>
                                    <p class="shrink-0 font-semibold text-ink"><x-money :value="$product->revenue" /></p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
