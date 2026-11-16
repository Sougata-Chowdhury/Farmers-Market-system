<x-app-layout title="My orders">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="My orders" subtitle="Track every order you have placed.">
            <a href="{{ route('home') }}" class="btn-secondary">Shop more</a>
        </x-page-header>

        <div class="mt-6 flex flex-wrap gap-2">
            @foreach (['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $value => $label)
                <a href="{{ route('orders.index', array_filter(['status' => $value ?: null])) }}"
                   @class([
                       'rounded-full border px-4 py-2 text-sm font-medium transition',
                       'border-emerald-600 bg-emerald-600 text-white' => $status === ($value ?: null),
                       'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-700' => $status !== ($value ?: null),
                   ])>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($orders->isEmpty())
            <div class="mt-6">
                <x-empty-state title="No orders here yet" description="When you place an order it will show up in this list.">
                    <a href="{{ route('home') }}" class="btn-primary">Browse produce</a>
                </x-empty-state>
            </div>
        @else
            <div class="mt-6 space-y-3">
                @foreach ($orders as $order)
                    <a href="{{ route('orders.show', $order) }}"
                       class="card flex flex-col gap-4 p-5 transition hover:border-emerald-300 hover:shadow-md sm:flex-row sm:items-center">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-semibold text-slate-900">Order #{{ $order->id }}</p>
                                <x-status-badge :status="$order->status" />
                            </div>
                            <p class="mt-1 text-sm text-slate-500">
                                Placed {{ $order->placed_at->format('d M Y, h:i A') }} &middot;
                                {{ $order->items_count }} {{ \Illuminate\Support\Str::plural('item', $order->items_count) }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4 sm:text-right">
                            <div>
                                <p class="text-xs uppercase tracking-wider text-slate-400">Total</p>
                                <p class="font-bold text-slate-900"><x-money :value="$order->total" /></p>
                            </div>
                            <span class="text-slate-300" aria-hidden="true">&rarr;</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">{{ $orders->links() }}</div>
        @endif
    </div>
</x-app-layout>
