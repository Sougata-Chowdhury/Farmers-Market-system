<x-app-layout title="Checkout">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Checkout" subtitle="Confirm your delivery details and place the order.">
            <a href="{{ route('cart.index') }}" class="btn-secondary">Back to cart</a>
        </x-page-header>

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_0.6fr] lg:items-start">
            @csrf

            <div class="card p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold text-slate-900">Delivery details</h2>
                <p class="mt-1 text-sm text-slate-500">We share these details with the farmer who fulfils your order.</p>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="name" class="label">Full name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $customer->name) }}" required
                               class="input mt-1" autocomplete="name">
                        @error('name') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="label">Mobile number</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $customer->phone) }}" required
                               class="input mt-1" placeholder="01XXXXXXXXX" autocomplete="tel">
                        @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="label">Delivery address</label>
                        <textarea id="address" name="address" rows="3" required class="input mt-1"
                                  autocomplete="street-address">{{ old('address', $customer->address) }}</textarea>
                        @error('address') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="notes" class="label">Delivery note <span class="text-slate-400">(optional)</span></label>
                        <textarea id="notes" name="notes" rows="2" class="input mt-1"
                                  placeholder="Landmark, preferred delivery window, etc.">{{ old('notes') }}</textarea>
                        @error('notes') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                    Payment is collected on delivery for this demo. The order is recorded immediately and stock is reserved.
                </div>
            </div>

            <aside class="card sticky top-24 p-5">
                <h2 class="font-display text-lg font-semibold text-slate-900">Review order</h2>

                <ul class="mt-4 space-y-3">
                    @foreach ($items as $item)
                        <li class="flex items-start justify-between gap-3 text-sm">
                            <div class="min-w-0">
                                <p class="truncate font-medium text-slate-800">{{ $item->product->name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $item->quantity }} &times; <x-money :value="$item->product->price" />
                                </p>
                            </div>
                            <p class="shrink-0 font-semibold text-slate-900">
                                <x-money :value="(float) $item->product->price * $item->quantity" />
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
