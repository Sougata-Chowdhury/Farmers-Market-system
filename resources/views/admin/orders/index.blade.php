<x-app-layout title="All orders">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="All orders" subtitle="Search, filter and progress customer orders.">
            <a href="{{ route('admin.exports.orders') }}" class="btn-secondary">Export CSV</a>
        </x-page-header>

        <form method="GET" action="{{ route('admin.orders.index') }}" class="mt-6 flex flex-wrap items-end gap-3">
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
                <a href="{{ route('admin.orders.index') }}" class="text-sm text-slate-500 hover:text-slate-900">Reset</a>
            @endif
        </form>

        @if ($orders->isEmpty())
            <div class="mt-6">
                <x-empty-state title="No orders matched" description="Try clearing the filters or searching a different term." />
            </div>
        @else
            <div class="card mt-6 overflow-hidden">
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
                                    <td class="font-semibold text-slate-900">#{{ $order->id }}</td>
                                    <td>
                                        <p class="font-medium text-slate-900">{{ $order->user?->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $order->user?->email }}</p>
                                    </td>
                                    <td class="text-slate-500">{{ $order->placed_at->format('d M Y, H:i') }}</td>
                                    <td class="text-center">{{ $order->items_count }}</td>
                                    <td class="text-right font-semibold"><x-money :value="$order->total" /></td>
                                    <td><x-status-badge :status="$order->status" /></td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-semibold text-emerald-700 hover:underline">Open</a>
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
