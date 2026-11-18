<x-app-layout title="Customers">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <p class="eyebrow mb-2">The address book</p>
        <x-page-header title="Customers" subtitle="Order history and lifetime value per customer." />

        @if ($customers->isEmpty())
            <div class="mt-6">
                <x-empty-state title="No customers yet" description="Customer accounts will appear here after registration." />
            </div>
        @else
            <div class="card mt-6 overflow-hidden">
                <div class="card-head">
                    <div>
                        <p class="eyebrow">Accounts</p>
                        <h2 class="mt-1 font-display text-xl font-semibold text-ink">Customer ledger</h2>
                    </div>
                    <span class="tag">{{ number_format($customers->total()) }} {{ \Illuminate\Support\Str::plural('account', $customers->total()) }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th scope="col">Customer</th>
                                <th scope="col">Contact</th>
                                <th scope="col">Address</th>
                                <th scope="col" class="text-center">Orders</th>
                                <th scope="col" class="text-right">Lifetime value</th>
                                <th scope="col">Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $customer)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-sm border border-forest-800 bg-forest-700 font-mono text-[11px] font-bold text-white">{{ $customer->initials }}</span>
                                            <p class="font-medium text-ink">{{ $customer->name }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-ink">{{ $customer->email }}</p>
                                        <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">{{ $customer->phone }}</p>
                                    </td>
                                    <td class="max-w-xs truncate text-ink-soft">{{ $customer->address ?: '—' }}</td>
                                    <td class="text-center font-mono text-[13px] text-ink-soft">{{ $customer->orders_count }}</td>
                                    <td class="text-right font-semibold"><x-money :value="$customer->lifetime_value ?? 0" /></td>
                                    <td class="font-mono text-[11px] uppercase tracking-label text-ink-faint">{{ $customer->created_at->format('d M Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8">{{ $customers->links() }}</div>
        @endif
    </div>
</x-app-layout>
