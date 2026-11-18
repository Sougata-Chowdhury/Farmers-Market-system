<x-app-layout title="Account settings">
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Account settings" subtitle="Keep your contact details up to date.">
            <a href="{{ route('dashboard') }}" class="btn-secondary">Back to dashboard</a>
        </x-page-header>

        <div class="mt-8 space-y-8">
            <section class="card p-5 sm:p-7">
                <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-dashed border-line-dark pb-3">
                    <div>
                        <p class="eyebrow">File card</p>
                        <h2 class="mt-1 font-display text-xl font-semibold text-ink">Profile information</h2>
                    </div>
                    <span class="tag">Used for deliveries</span>
                </div>
                <p class="mt-3 max-w-prose text-sm leading-relaxed text-ink-soft">
                    Keep this current so orders, receipts and account notices reach the right person.
                </p>

                <form method="POST" action="{{ route('profile.update') }}" class="mt-6 grid gap-5 sm:grid-cols-2">
                    @csrf
                    @method('patch')

                    <div>
                        <label for="name" class="label">Full name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="input mt-1">
                        @error('name') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="label">Mobile number</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" required class="input mt-1">
                        @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="email" class="label">Email address</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="input mt-1">
                        @error('email') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="label">Address</label>
                        <input id="address" name="address" type="text" value="{{ old('address', $user->address) }}" class="input mt-1">
                        @error('address') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2 border-t border-dashed border-line pt-5">
                        <button type="submit" class="btn-primary">Save changes</button>
                    </div>
                </form>
            </section>

            @if ($user->isCustomer())
                @php($defaultAddress = $user->defaultAddress())

                <section class="card p-5 sm:p-7">
                    <div class="flex flex-wrap items-end justify-between gap-3 border-b border-dashed border-line-dark pb-3">
                        <div>
                            <p class="eyebrow">Delivery ledger</p>
                            <h2 class="mt-1 font-display text-xl font-semibold text-ink">Delivery addresses</h2>
                            <p class="mt-1 font-mono text-[11px] uppercase tracking-label text-ink-soft">
                                {{ $user->addresses()->count() }} saved
                                @if ($defaultAddress)
                                    &middot; default is {{ $defaultAddress->label }}
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('addresses.index') }}" class="btn-secondary">Manage addresses</a>
                    </div>

                    @if ($defaultAddress)
                        <div class="mt-5 rounded-md border border-line bg-paper-light p-4 text-sm">
                            <p class="flex items-center gap-2 font-display text-base font-semibold text-ink">
                                {{ $defaultAddress->label }}
                                <span class="stamp stamp-green !px-2 !py-0.5 !text-[9px]">Default</span>
                            </p>
                            <p class="mt-2 leading-relaxed text-ink-soft">
                                {{ $defaultAddress->recipient_name }} &middot; {{ $defaultAddress->phone }}<br>
                                {{ $defaultAddress->address_line }}@if ($defaultAddress->area), {{ $defaultAddress->area }}@endif
                            </p>
                        </div>
                    @else
                        <p class="mt-5 text-sm text-ink-soft">
                            Add an address to check out faster next time.
                            <a href="{{ route('addresses.index') }}" class="link">Add one now</a>.
                        </p>
                    @endif
                </section>
            @endif

            <section class="card p-5 sm:p-7">
                <div class="border-b border-dashed border-line-dark pb-3">
                    <p class="eyebrow">Security</p>
                    <h2 class="mt-1 font-display text-xl font-semibold text-ink">Change password</h2>
                </div>
                <p class="mt-3 max-w-prose text-sm leading-relaxed text-ink-soft">
                    Use a long password you don't reuse anywhere else.
                </p>

                <form method="POST" action="{{ route('password.update') }}" class="mt-6 grid gap-5 sm:grid-cols-3">
                    @csrf
                    @method('put')

                    <div>
                        <label for="current_password" class="label">Current password</label>
                        <input id="current_password" name="current_password" type="password" autocomplete="current-password" class="input mt-1">
                        @foreach ($errors->get('current_password') as $message)
                            <p class="field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div>
                        <label for="password" class="label">New password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" class="input mt-1">
                        @foreach ($errors->get('password') as $message)
                            <p class="field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <div>
                        <label for="password_confirmation" class="label">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="input mt-1">
                    </div>

                    <div class="sm:col-span-3 border-t border-dashed border-line pt-5">
                        <button type="submit" class="btn-primary">Update password</button>
                    </div>
                </form>
            </section>

            <section class="rounded-lg border-2 border-dashed border-clay-300 bg-clay-50/60 p-5 sm:p-7">
                <div class="flex flex-wrap items-center gap-3">
                    <p class="eyebrow !text-clay-700">Handle with care</p>
                    <span class="stamp !text-[9px]">Permanent</span>
                </div>
                <h2 class="mt-2 font-display text-xl font-semibold text-clay-800">Delete account</h2>
                <p class="mt-2 max-w-prose text-sm leading-relaxed text-clay-700">
                    This permanently removes your account. Orders you placed stay in the system for farmers' records.
                </p>

                <form id="delete-account-form" method="POST" action="{{ route('profile.destroy') }}" class="mt-5 flex flex-wrap items-end gap-3">
                    @csrf
                    @method('delete')

                    <div class="w-full sm:w-64">
                        <label for="delete_password" class="label !text-clay-800">Confirm with your password</label>
                        <input id="delete_password" name="password" type="password" autocomplete="current-password"
                               class="input mt-1 border-clay-300 bg-white focus:border-clay-500 focus:ring-clay-500">
                        @foreach ($errors->get('password') as $message)
                            <p class="field-error">{{ $message }}</p>
                        @endforeach
                    </div>

                    <button type="button" class="btn-danger" x-on:click="$dispatch('open-modal', 'delete-account')">
                        Delete my account
                    </button>
                </form>

                <x-modal name="delete-account" max-width="md" label="Delete your account?">
                    <div class="p-6">
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-sm border border-clay-300 bg-clay-50 text-clay-700">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </span>
                            <div>
                                <p class="eyebrow !text-clay-700">Please confirm</p>
                                <h2 class="mt-1 font-display text-lg font-semibold text-ink">Delete your account?</h2>
                                <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                                    This permanently removes your profile, addresses and saved items. Past orders stay with the farmers who fulfilled them.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <button type="button" class="btn-secondary"
                                    x-on:click="$dispatch('close-modal', 'delete-account')">
                                Keep my account
                            </button>
                            <button type="submit" form="delete-account-form" class="btn-danger">
                                Yes, delete permanently
                            </button>
                        </div>
                    </div>
                </x-modal>
            </section>
        </div>
    </div>
</x-app-layout>
