<x-app-layout title="My orders">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="My orders" subtitle="Track every order you have placed.">
            <a href="{{ route('home') }}" class="btn-secondary">Shop more</a>
        </x-page-header>

        <div class="mt-6 flex flex-wrap gap-x-5 gap-y-2 border-b border-line-dark">
            @foreach (['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $value => $label)
                <a href="{{ route('orders.index', array_filter(['status' => $value ?: null])) }}"
                   @class([
                       'border-b-2 pb-2 font-mono text-[10px] font-bold uppercase tracking-label transition',
                       'border-clay-500 text-ink' => $status === ($value ?: null),
                       'border-transparent text-ink-soft hover:text-ink' => $status !== ($value ?: null),
                   ])
                   @if ($status === ($value ?: null)) aria-current="page" @endif>
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
                    <div class="card flex flex-col gap-4 p-5 transition hover:border-ink hover:shadow-md sm:flex-row sm:items-center">
                        <a href="{{ route('orders.show', $order) }}" class="flex flex-1 flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-display text-lg font-semibold text-ink">Order #{{ $order->id }}</p>
                                    <x-status-badge :status="$order->status" />
                                </div>
                                <p class="mt-1 font-mono text-[10px] uppercase tracking-label text-ink-faint">
                                    Placed {{ $order->placed_at->format('d M Y, h:i A') }} &middot;
                                    {{ $order->items_count }} {{ \Illuminate\Support\Str::plural('item', $order->items_count) }}
                                </p>
                            </div>

                            <div class="flex items-center gap-4 sm:text-right">
                                <div>
                                    <p class="font-mono text-[9px] uppercase tracking-label text-ink-faint">Total</p>
                                    <p class="font-display text-lg font-bold text-ink"><x-money :value="$order->total" /></p>
                                </div>
                                <span class="text-line-dark" aria-hidden="true">&rarr;</span>
                            </div>
                        </a>

                        @if ($order->status !== \App\Enums\OrderStatus::Cancelled)
                            <form method="POST" action="{{ route('orders.reorder', $order) }}" class="sm:ml-auto">
                                @csrf
                                <button type="submit" class="btn-secondary w-full whitespace-nowrap sm:w-auto">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    Buy again
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-8">{{ $orders->links() }}</div>
        @endif
    </div>
</x-app-layout>
