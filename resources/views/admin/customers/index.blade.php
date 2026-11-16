<x-app-layout title="Customers">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Customers" subtitle="Order history and lifetime value per customer." />

        @if ($customers->isEmpty())
            <div class="mt-6">
                <x-empty-state title="No customers yet" description="Customer accounts will appear here after registration." />
            </div>
        @else
            <div class="card mt-6 overflow-hidden">
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
                                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-800 text-xs font-bold text-white">{{ $customer->initials }}</span>
                                            <p class="font-medium text-slate-900">{{ $customer->name }}</p>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-slate-700">{{ $customer->email }}</p>
                                        <p class="text-xs text-slate-500">{{ $customer->phone }}</p>
                                    </td>
                                    <td class="max-w-xs truncate text-slate-600">{{ $customer->address ?: '—' }}</td>
                                    <td class="text-center">{{ $customer->orders_count }}</td>
                                    <td class="text-right font-semibold"><x-money :value="$customer->lifetime_value ?? 0" /></td>
                                    <td class="text-slate-500">{{ $customer->created_at->format('d M Y') }}</td>
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
