<x-app-layout title="Delivery addresses">
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Delivery addresses" subtitle="Save the places you order to most and check out in one tap.">
            <a href="{{ route('profile.edit') }}" class="btn-secondary">Account settings</a>
            <a href="{{ route('home') }}" class="btn-primary">Continue shopping</a>
        </x-page-header>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_22rem] lg:items-start">
            <section class="space-y-4">
                @forelse ($addresses as $address)
                    <article class="card p-5" x-data>
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-display text-xl font-semibold text-ink">{{ $address->label }}</h2>
                                    @if ($address->is_default)
                                        <span class="tag !border-forest-700/40 !bg-forest-50 !text-forest-700">Default</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm font-medium text-ink">{{ $address->recipient_name }}</p>
                                <p class="font-mono text-[11px] text-ink-soft">{{ $address->phone }}</p>
                                <p class="mt-1 text-sm leading-relaxed text-ink-soft">
                                    {{ $address->address_line }}@if ($address->area), {{ $address->area }}@endif
                                </p>
                            </div>

                            <div class="flex shrink-0 flex-wrap items-center gap-2">
                                @unless ($address->is_default)
                                    <form method="POST" action="{{ route('addresses.default', $address) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-secondary !px-3 !py-1.5 text-xs">Make default</button>
                                    </form>
                                @endunless

                                <button type="button" class="btn-danger !px-3 !py-1.5 text-xs"
                                        x-on:click="$dispatch('open-modal', 'delete-address-{{ $address->id }}')">
                                    Remove
                                </button>
                            </div>
                        </div>

                        <x-modal name="delete-address-{{ $address->id }}" max-width="md" focusable>
                            <div class="p-6">
                                <p class="eyebrow">Remove address</p>
                                <h3 class="mt-1 font-display text-lg font-semibold text-ink">Remove this address?</h3>
                                <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                                    <span class="font-medium text-ink">{{ $address->label }}</span> —
                                    {{ $address->address_line }} will be removed from your saved addresses.
                                    Past orders keep the delivery details they were placed with.
                                </p>

                                <div class="mt-6 flex flex-col-reverse justify-end gap-2 border-t border-dashed border-line pt-5 sm:flex-row">
                                    <button type="button" class="btn-secondary" x-on:click="$dispatch('close')">Keep address</button>

                                    <form method="POST" action="{{ route('addresses.destroy', $address) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Remove address</button>
                                    </form>
                                </div>
                            </div>
                        </x-modal>
                    </article>
                @empty
                    <x-empty-state
                        title="No saved addresses yet"
                        description="Add your home or office address and it will be ready at checkout.">
                        <a href="#new-address" class="btn-primary">Add an address</a>
                    </x-empty-state>
                @endforelse
            </section>

            <aside id="new-address" class="card p-5">
                <p class="eyebrow">A new place</p>
                <h2 class="mt-1 font-display text-2xl font-semibold text-ink">Add an address</h2>
                <p class="mt-1 text-sm text-ink-soft">Saved addresses can be edited by removing and adding a new one.</p>

                <form method="POST" action="{{ route('addresses.store') }}" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label for="label" class="label">Label</label>
                        <input id="label" name="label" type="text" value="{{ old('label', 'Home') }}"
                               class="input mt-1" placeholder="Home, Office, Parents' house…">
                        @error('label') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="recipient_name" class="label">Recipient name</label>
                        <input id="recipient_name" name="recipient_name" type="text" value="{{ old('recipient_name', auth()->user()->name) }}"
                               required class="input mt-1" autocomplete="name">
                        @error('recipient_name') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="label">Mobile number</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', auth()->user()->phone) }}"
                               required class="input mt-1" placeholder="01XXXXXXXXX" autocomplete="tel">
                        @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="address_line" class="label">Street address</label>
                        <textarea id="address_line" name="address_line" rows="3" required class="input mt-1"
                                  autocomplete="street-address">{{ old('address_line') }}</textarea>
                        @error('address_line') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="area" class="label">Area or city <span class="text-ink-faint">(optional)</span></label>
                        <input id="area" name="area" type="text" value="{{ old('area') }}" class="input mt-1"
                               placeholder="e.g. Dhanmondi, Dhaka">
                        @error('area') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-ink">
                        <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $addresses->isEmpty()))
                               class="h-4 w-4 rounded border-line-dark text-forest-700 focus:ring-forest-600">
                        Use as my default delivery address
                    </label>

                    <button type="submit" class="btn-primary w-full">Save address</button>
                </form>
            </aside>
        </div>
    </div>
</x-app-layout>
