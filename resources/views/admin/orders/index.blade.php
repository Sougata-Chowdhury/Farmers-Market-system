<x-app-layout title="All orders">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <p class="eyebrow mb-2">The order book</p>
        <x-page-header title="All orders" subtitle="Search, filter and progress customer orders.">
            <a href="{{ route('admin.exports.orders') }}" class="btn-secondary" data-csv-export data-filename="orders.csv" download>Export CSV</a>
        </x-page-header>

        <form method="GET" action="{{ route('admin.orders.index') }}" class="card mt-6 flex flex-wrap items-end gap-3 p-4">
            <p class="eyebrow w-full">Filter the ledger</p>

            <div>
                <label for="q" class="label">Search</label>
                <input id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Order #, name or email"
                       class="input mt-1 w-64">
            </div>

            <div>
                <label for="status" class="label">Status</label>
                <select id="status" name="status" class="input mt-1 w-44">
                    <option value="">All statuses</option>
                    @foreach (\App\Enums\OrderStatus::cases() as $case)
                        <option value="{{ $case->value }}" @selected(($filters['status'] ?? '') === $case->value)>{{ $case->label() }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-primary">Apply</button>
            @if (($filters['q'] ?? '') || ($filters['status'] ?? ''))
                <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center px-1 py-2.5 font-mono text-[10px] font-bold uppercase tracking-label text-ink-faint transition hover:text-clay-600">Reset</a>
            @endif
        </form>

        @if ($orders->isEmpty())
            <div class="mt-6">
                <x-empty-state title="No orders matched" description="Try clearing the filters or searching a different term." />
            </div>
        @else
            <div class="card mt-6 overflow-hidden">
                <div class="card-head">
                    <div>
                        <p class="eyebrow">Every order, in sequence</p>
                        <h2 class="mt-1 font-display text-xl font-semibold text-ink">Order ledger</h2>
                    </div>
                    <span class="tag">{{ number_format($orders->total()) }} {{ \Illuminate\Support\Str::plural('entry', $orders->total()) }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th scope="col">Order</th>
                                <th scope="col">Customer</th>
                                <th scope="col">Placed</th>
                                <th scope="col" class="text-center">Items</th>
                                <th scope="col" class="text-right">Total</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-sm font-bold text-clay-700 hover:underline">#{{ $order->id }}</a>
                                    </td>
                                    <td>
                                        <p class="font-medium text-ink">{{ $order->user?->name }}</p>
                                        <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">{{ $order->user?->email }}</p>
                                    </td>
                                    <td class="font-mono text-[11px] uppercase tracking-label text-ink-faint">{{ $order->placed_at->format('d M Y, H:i') }}</td>
                                    <td class="text-center font-mono text-[13px] text-ink-soft">{{ $order->items_count }}</td>
                                    <td class="text-right font-semibold"><x-money :value="$order->total" /></td>
                                    <td><x-status-badge :status="$order->status" /></td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-forest-700 hover:underline">Open &rarr;</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8">{{ $orders->links() }}</div>
        @endif
    </div>
</x-app-layout>
