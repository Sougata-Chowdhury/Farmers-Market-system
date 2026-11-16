<x-app-layout title="Farmers">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Farmers" subtitle="Earnings, sales and payout accounts for every seller." />

        @if ($farmers->isEmpty())
            <div class="mt-6">
                <x-empty-state title="No farmers yet" description="Farmers appear here after they create an account." />
            </div>
        @else
            <div class="card mt-6 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th scope="col">Farmer</th>
                                <th scope="col">Contact</th>
                                <th scope="col" class="text-center">Products</th>
                                <th scope="col" class="text-center">Orders</th>
                                <th scope="col" class="text-center">Units</th>
                                <th scope="col" class="text-right">Earnings</th>
                                <th scope="col">Payout account</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($farmers as $farmer)
                                @php($sale = $sales->get($farmer->id))
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-emerald-600 text-xs font-bold text-white">{{ $farmer->initials }}</span>
                                            <div>
                                                <p class="font-medium text-slate-900">{{ $farmer->name }}</p>
                                                <p class="text-xs text-slate-500">Joined {{ $farmer->created_at->format('M Y') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-slate-700">{{ $farmer->email }}</p>
                                        <p class="text-xs text-slate-500">{{ $farmer->phone }}</p>
                                    </td>
                                    <td class="text-center">{{ $farmer->products_count }}</td>
                                    <td class="text-center">{{ (int) ($sale->orders ?? 0) }}</td>
                                    <td class="text-center">{{ number_format((int) ($sale->units ?? 0)) }}</td>
                                    <td class="text-right font-semibold"><x-money :value="$sale->earnings ?? 0" /></td>
                                    <td>
                                        @if ($farmer->farmerProfile?->account_no)
                                            <p class="text-sm font-medium text-slate-800">{{ $farmer->farmerProfile->bank_name }}</p>
                                            <p class="text-xs text-slate-500">
                                                {{ $farmer->farmerProfile->branch }} &middot; ****{{ substr($farmer->farmerProfile->account_no, -4) }}
                                            </p>
                                        @else
                                            <span class="text-xs font-medium text-amber-700">Not provided</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8">{{ $farmers->links() }}</div>
        @endif
    </div>
</x-app-layout>
