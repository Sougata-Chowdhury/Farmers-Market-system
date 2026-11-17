<x-app-layout title="Admin dashboard">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Platform overview" subtitle="Revenue, orders and marketplace health at a glance.">
            <a href="{{ route('admin.exports.orders') }}" class="btn-secondary">Export orders CSV</a>
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
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Customers</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($stats['customers']) }}</p>
                </div>
                <a href="{{ route('admin.customers.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">View</a>
            </div>
            <div class="card flex items-center justify-between p-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Farmers</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($stats['farmers']) }}</p>
                </div>
                <a href="{{ route('admin.farmers.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">View</a>
            </div>
            <div class="card flex items-center justify-between p-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Products</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($stats['products']) }}</p>
                </div>
                <a href="{{ route('admin.products.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">Moderate</a>
            </div>
            <div class="card flex items-center justify-between p-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Pending orders</p>
                    <p class="mt-1 text-2xl font-bold text-amber-600">{{ number_format($stats['pending']) }}</p>
                </div>
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="text-sm font-medium text-emerald-700 hover:underline">Review</a>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.5fr_0.5fr] lg:items-start">
            <section class="card p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold text-slate-900">Revenue, last 30 days</h2>
                        <p class="mt-1 text-sm text-slate-500">Cancelled orders are excluded from the totals.</p>
                    </div>
                </div>
                <div class="mt-4">
                    <x-line-chart :points="$revenueSeries" label="Daily revenue" :height="190" />
                </div>
            </section>

            <section class="card p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold text-slate-900">Orders by status</h2>
                <p class="mt-1 text-sm text-slate-500">Every order ever placed.</p>
                <div class="mt-4">
                    <x-donut-chart :segments="$statusSegments" title="Orders by status" unit="orders" />
                </div>
            </section>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_0.6fr] lg:items-start">
            <section class="card overflow-hidden">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Recent orders</h2>
                    <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">All orders</a>
                </div>

                @if ($recentOrders->isEmpty())
                    <p class="px-5 py-8 text-center text-sm text-slate-500">No orders have been placed yet.</p>
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
                                            <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-emerald-700 hover:underline">
                                                #{{ $order->id }}
                                            </a>
                                        </td>
                                        <td>{{ $order->user?->name }}</td>
                                        <td class="text-slate-500">{{ $order->placed_at->format('d M, H:i') }}</td>
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
                    <h2 class="font-display text-lg font-semibold text-slate-900">Top categories</h2>
                    @if ($topCategories->isEmpty())
                        <p class="mt-3 text-sm text-slate-500">No sales yet.</p>
                    @else
                        @php($categoryMax = max(1, (float) $topCategories->max('revenue')))
                        <ul class="mt-3 space-y-3">
                            @foreach ($topCategories as $category)
                                <li>
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="font-medium text-slate-700">{{ $category->name }}</span>
                                        <span class="font-semibold text-slate-900"><x-money :value="$category->revenue" /></span>
                                    </div>
                                    <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ round(((float) $category->revenue / $categoryMax) * 100) }}%"></div>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-400">{{ number_format((int) $category->units) }} units sold</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="card p-5">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Best sellers</h2>
                    @if ($topProducts->isEmpty())
                        <p class="mt-3 text-sm text-slate-500">No sales yet.</p>
                    @else
                        <ul class="mt-3 divide-y divide-slate-100">
                            @foreach ($topProducts as $product)
                                <li class="flex items-center justify-between gap-3 py-2 text-sm">
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-slate-900">{{ $product->product_name }}</p>
                                        <p class="text-xs text-slate-500">{{ number_format((int) $product->units) }} units</p>
                                    </div>
                                    <p class="shrink-0 font-semibold text-slate-900"><x-money :value="$product->revenue" /></p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
