<x-app-layout title="Checkout">
    @php
        $selectedAddress = old('address_id', $defaultAddress?->id);
        $startWithNew = $addresses->isEmpty() || ($errors->any() && blank(old('address_id')));
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Checkout" subtitle="Confirm your delivery details, pick a payment method and place the order.">
            <a href="{{ route('cart.index') }}" class="btn-secondary">Back to cart</a>
        </x-page-header>

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_0.6fr] lg:items-start"
              x-data="{ choice: '{{ $startWithNew ? 'new' : $selectedAddress }}' }">
            @csrf

            <div class="space-y-6">
                <section class="card p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="font-display text-lg font-semibold text-slate-900">Delivery address</h2>
                            <p class="mt-1 text-sm text-slate-500">We share these details with the farmer who fulfils your order.</p>
                        </div>
                        <a href="{{ route('addresses.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">Manage addresses</a>
                    </div>

                    @error('address_id')
                        <p class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $message }}</p>
                    @enderror

                    @if ($addresses->isNotEmpty())
                        <ul class="mt-4 space-y-3" role="radiogroup" aria-label="Saved delivery addresses">
                            @foreach ($addresses as $address)
                                <li>
                                    <label class="relative flex cursor-pointer gap-3 rounded-xl border bg-white p-4 transition"
                                           :class="choice === '{{ $address->id }}' ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-slate-200 hover:border-emerald-300'">
                                        <input type="radio" name="address_id" value="{{ $address->id }}"
                                               x-model="choice" class="mt-1 h-4 w-4 shrink-0 text-emerald-600 focus:ring-emerald-500">

                                        <span class="min-w-0 flex-1">
                                            <span class="flex flex-wrap items-center gap-2">
                                                <span class="text-sm font-semibold text-slate-900">{{ $address->label }}</span>
                                                @if ($address->is_default)
                                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Default</span>
                                                @endif
                                                <span class="text-xs text-slate-400">{{ $address->phone }}</span>
                                            </span>
                                            <span class="mt-1 block text-sm text-slate-600">
                                                {{ $address->recipient_name }} &middot;
                                                {{ $address->address_line }}@if ($address->area), {{ $address->area }}@endif
                                            </span>
                                        </span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <label class="mt-3 flex cursor-pointer items-center gap-3 rounded-xl border p-4 transition"
                           :class="choice === 'new' ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-slate-200 hover:border-emerald-300'">
                        <input type="radio" name="address_id" value="" x-model="choice"
                               class="h-4 w-4 shrink-0 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-sm font-semibold text-slate-900">
                            {{ $addresses->isEmpty() ? 'Enter a delivery address' : 'Deliver to a new address' }}
                        </span>
                    </label>

                    <div x-show="choice === 'new'" x-cloak class="mt-5 border-t border-slate-100 pt-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="name" class="label">Full name</label>
                                <input id="name" name="name" type="text" value="{{ old('name', $customer->name) }}"
                                       class="input mt-1" autocomplete="name" :disabled="choice !== 'new'">
                                @error('name') <p class="field-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="phone" class="label">Mobile number</label>
                                <input id="phone" name="phone" type="tel" value="{{ old('phone', $customer->phone) }}"
                                       class="input mt-1" placeholder="01XXXXXXXXX" autocomplete="tel" :disabled="choice !== 'new'">
                                @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="address" class="label">Delivery address</label>
                                <textarea id="address" name="address" rows="3" class="input mt-1"
                                          autocomplete="street-address" :disabled="choice !== 'new'">{{ old('address', $customer->address) }}</textarea>
                                @error('address') <p class="field-error">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <label class="flex items-start gap-3 text-sm text-slate-700">
                                    <input type="checkbox" name="save_address" value="1" @checked(old('save_address'))
                                           class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                           :disabled="choice !== 'new'">
                                    <span>Save this address for next time</span>
                                </label>

                                <div class="mt-3">
                                    <label for="label" class="label text-xs">Address label <span class="text-slate-400">(optional)</span></label>
                                    <input id="label" name="label" type="text" value="{{ old('label', 'Home') }}"
                                           class="input mt-1 max-w-xs" placeholder="Home, Office…" :disabled="choice !== 'new'">
                                    @error('label') <p class="field-error">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="card p-5 sm:p-6">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Payment method</h2>
                    <p class="mt-1 text-sm text-slate-500">This demo records the choice on your order — no real money moves.</p>

                    @error('payment_method')
                        <p class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">{{ $message }}</p>
                    @enderror

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @foreach ($paymentMethods as $method)
                            <label class="relative flex cursor-pointer flex-col gap-2 rounded-xl border p-4 transition has-[:checked]:border-emerald-500 has-[:checked]:ring-1 has-[:checked]:ring-emerald-500 hover:border-emerald-300">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-2 text-sm font-semibold text-slate-900">
                                        @if ($method === \App\Enums\PaymentMethod::Cod)
                                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                            </svg>
                                        @endif
                                        {{ $method->label() }}
                                    </span>
                                    <input type="radio" name="payment_method" value="{{ $method->value }}"
                                           @checked(old('payment_method', 'cod') === $method->value)
                                           class="h-4 w-4 text-emerald-600 focus:ring-emerald-500">
                                </span>
                                <span class="text-xs leading-relaxed text-slate-500">{{ $method->description() }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <label for="notes" class="label">Delivery note <span class="text-slate-400">(optional)</span></label>
                        <textarea id="notes" name="notes" rows="2" class="input mt-1"
                                  placeholder="Landmark, preferred delivery window, etc.">{{ old('notes') }}</textarea>
                        @error('notes') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </section>
            </div>

            <aside class="card sticky top-24 p-5">
                <h2 class="font-display text-lg font-semibold text-slate-900">Review order</h2>

                <ul class="mt-4 space-y-3">
                    @foreach ($items as $item)
                        <li class="flex items-start justify-between gap-3 text-sm">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-800">{{ $item->product?->name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $item->quantity }} &times; <x-money :value="$item->product?->price ?? 0" />
                                </p>
                            </div>
                            <p class="shrink-0 font-semibold text-slate-900">
                                <x-money :value="(float) ($item->product?->price ?? 0) * $item->quantity" />
                            </p>
                        </li>
                    @endforeach
                </ul>

                <dl class="mt-4 space-y-2 border-t border-slate-200 pt-4 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Subtotal</dt>
                        <dd class="font-medium text-slate-900"><x-money :value="$totals['subtotal']" /></dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Platform fee ({{ (int) (config('marketplace.platform_fee') * 100) }}%)</dt>
                        <dd class="font-medium text-slate-900"><x-money :value="$totals['fee']" /></dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-200 pt-3 text-base">
                        <dt class="font-semibold text-slate-900">Total payable</dt>
                        <dd class="font-bold text-slate-900"><x-money :value="$totals['total']" /></dd>
                    </div>
                </dl>

                <button type="submit" class="btn-primary mt-5 w-full">Place order</button>

                <p class="mt-3 text-xs text-slate-500">
                    Farmers receive the full item subtotal. The platform fee covers payment processing and support.
                </p>
            </aside>
        </form>
    </div>
</x-app-layout>
