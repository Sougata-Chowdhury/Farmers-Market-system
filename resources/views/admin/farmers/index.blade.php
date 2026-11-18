<x-app-layout title="Farmers">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <p class="eyebrow mb-2">The growers</p>
        <x-page-header title="Farmers" subtitle="Earnings, sales and payout accounts for every seller." />

        @if ($farmers->isEmpty())
            <div class="mt-6">
                <x-empty-state title="No farmers yet" description="Farmers appear here after they create an account." />
            </div>
        @else
            <div class="card mt-6 overflow-hidden">
                <div class="card-head">
                    <div>
                        <p class="eyebrow">The stallholders</p>
                        <h2 class="mt-1 font-display text-xl font-semibold text-ink">Farmer ledger</h2>
                    </div>
                    <span class="tag">{{ number_format($farmers->total()) }} {{ \Illuminate\Support\Str::plural('farmer', $farmers->total()) }}</span>
                </div>

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
                                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-sm border border-ochre-800 bg-ochre-600 font-mono text-[11px] font-bold text-white">{{ $farmer->initials }}</span>
                                            <div>
                                                <p class="font-medium text-ink">{{ $farmer->name }}</p>
                                                <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">Joined {{ $farmer->created_at->format('M Y') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-ink">{{ $farmer->email }}</p>
                                        <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">{{ $farmer->phone }}</p>
                                    </td>
                                    <td class="text-center font-mono text-[13px] text-ink-soft">{{ $farmer->products_count }}</td>
                                    <td class="text-center font-mono text-[13px] text-ink-soft">{{ (int) ($sale->orders ?? 0) }}</td>
                                    <td class="text-center font-mono text-[13px] text-ink-soft">{{ number_format((int) ($sale->units ?? 0)) }}</td>
                                    <td class="text-right font-semibold"><x-money :value="$sale->earnings ?? 0" /></td>
                                    <td>
                                        @if ($farmer->farmerProfile?->account_no)
                                            <p class="text-sm font-medium text-ink">{{ $farmer->farmerProfile->bank_name }}</p>
                                            <p class="font-mono text-[10px] uppercase tracking-label text-ink-faint">
                                                {{ $farmer->farmerProfile->branch }} &middot; ****{{ substr($farmer->farmerProfile->account_no, -4) }}
                                            </p>
                                        @else
                                            <span class="inline-flex items-center rounded-sm border border-ochre-300 bg-ochre-50 px-2 py-0.5 font-mono text-[9px] font-bold uppercase tracking-label text-ochre-800">Not provided</span>
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
