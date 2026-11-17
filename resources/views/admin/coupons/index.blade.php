<x-app-layout title="Coupons">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Coupons" subtitle="Discount codes customers apply in the cart or at checkout.">
            <a href="#new-coupon" class="btn-primary">New coupon</a>
        </x-page-header>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Total coupons" :value="number_format($stats['total'])" hint="All codes ever created" />
            <x-stat-card label="Active now" :value="number_format($stats['active'])" hint="Enabled and within limits" />
            <x-stat-card label="Redemptions" :value="number_format($stats['redemptions'])" hint="Orders placed with a coupon" />
            <x-stat-card label="Discount given" :value="config('marketplace.currency.symbol').number_format($stats['discount_given'], 2)" hint="Across paid orders" />
        </div>

        <div class="mt-6 grid gap-6 xl:grid-cols-[1.6fr_0.9fr] xl:items-start">
            <div class="card overflow-hidden">
                @if ($coupons->isEmpty())
                    <div class="p-5">
                        <x-empty-state title="No coupons yet" description="Create your first discount code and it will appear here." />
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table-base">
                            <thead>
                                <tr>
                                    <th scope="col">Code</th>
                                    <th scope="col">Discount</th>
                                    <th scope="col" class="text-right">Min spend</th>
                                    <th scope="col" class="text-center">Usage</th>
                                    <th scope="col">Expires</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($coupons as $coupon)
                                    <tr x-data>
                                        <td>
                                            <p class="font-mono text-sm font-semibold tracking-wide text-slate-900">{{ $coupon->code }}</p>
                                            @if ($coupon->description)
                                                <p class="mt-0.5 max-w-xs truncate text-xs text-slate-500">{{ $coupon->description }}</p>
                                            @endif
                                        </td>
                                        <td class="font-medium text-slate-800">
                                            {{ $coupon->type->describe((float) $coupon->value) }}
                                        </td>
                                        <td class="text-right text-slate-600">
                                            @if ((float) $coupon->min_spend > 0)
                                                <x-money :value="$coupon->min_spend" />
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-center text-slate-600">
                                            {{ number_format($coupon->used_count) }}@if ($coupon->max_uses)/{{ number_format($coupon->max_uses) }}@endif
                                            <span class="block text-[11px] text-slate-400">{{ $coupon->orders_count }} {{ \Illuminate\Support\Str::plural('order', $coupon->orders_count) }}</span>
                                        </td>
                                        <td class="text-slate-600">
                                            {{ $coupon->expires_at?->format('d M Y') ?? 'Never' }}
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $coupon->statusClasses() }}">
                                                {{ $coupon->statusLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex items-center justify-end gap-2">
                                                <form method="POST" action="{{ route('admin.coupons.toggle', $coupon) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-xs font-semibold text-emerald-700 hover:underline">
                                                        {{ $coupon->is_active ? 'Disable' : 'Enable' }}
                                                    </button>
                                                </form>

                                                <button type="button" class="text-xs font-semibold text-slate-500 transition hover:text-rose-600"
                                                        x-on:click="$dispatch('open-modal', 'delete-coupon-{{ $coupon->id }}')">
                                                    Delete
                                                </button>
                                            </div>

                                            <x-modal name="delete-coupon-{{ $coupon->id }}" max-width="md" focusable>
                                                <div class="p-6">
                                                    <h3 class="font-display text-lg font-semibold text-slate-900">Delete {{ $coupon->code }}?</h3>
                                                    <p class="mt-2 text-sm text-slate-500">
                                                        Past orders keep their discount and code. This coupon can no longer be redeemed.
                                                    </p>
                                                    <div class="mt-6 flex justify-end gap-2">
                                                        <button type="button" class="btn-secondary" x-on:click="$dispatch('close')">Keep coupon</button>
                                                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-danger">Delete coupon</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </x-modal>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <aside id="new-coupon" class="card p-5 xl:sticky xl:top-24">
                <h2 class="font-display text-lg font-semibold text-slate-900">Create a coupon</h2>
                <p class="mt-1 text-sm text-slate-500">Codes are case-insensitive and stored in uppercase.</p>

                <form method="POST" action="{{ route('admin.coupons.store') }}" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label for="code" class="label">Code</label>
                        <input id="code" name="code" type="text" value="{{ old('code') }}" required
                               class="input mt-1 font-mono uppercase" placeholder="FRESH10" autocomplete="off">
                        @error('code') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="type" class="label">Type</label>
                            <select id="type" name="type" class="input mt-1">
                                @foreach (\App\Enums\CouponType::cases() as $type)
                                    <option value="{{ $type->value }}" @selected(old('type', 'percent') === $type->value)>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                            @error('type') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="value" class="label">Value</label>
                            <input id="value" name="value" type="number" step="0.01" min="0.01" value="{{ old('value') }}" required
                                   class="input mt-1" placeholder="10">
                            @error('value') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="min_spend" class="label">Min spend</label>
                            <input id="min_spend" name="min_spend" type="number" step="0.01" min="0" value="{{ old('min_spend', 0) }}"
                                   class="input mt-1">
                            @error('min_spend') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="max_uses" class="label">Usage cap</label>
                            <input id="max_uses" name="max_uses" type="number" min="1" value="{{ old('max_uses') }}"
                                   class="input mt-1" placeholder="Unlimited">
                            @error('max_uses') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="expires_at" class="label">Expires on <span class="text-slate-400">(optional)</span></label>
                        <input id="expires_at" name="expires_at" type="date" value="{{ old('expires_at') }}" class="input mt-1">
                        @error('expires_at') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="label">Description <span class="text-slate-400">(optional)</span></label>
                        <input id="description" name="description" type="text" value="{{ old('description') }}"
                               class="input mt-1" placeholder="Festive vegetable offer">
                        @error('description') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        Active immediately
                    </label>

                    <button type="submit" class="btn-primary w-full">Create coupon</button>
                </form>
            </aside>
        </div>

        @if ($coupons->hasPages())
            <div class="mt-8">{{ $coupons->links() }}</div>
        @endif
    </div>
</x-app-layout>
