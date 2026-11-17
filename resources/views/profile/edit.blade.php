<x-app-layout title="Account settings">
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Account settings" subtitle="Keep your contact details up to date.">
            <a href="{{ route('dashboard') }}" class="btn-secondary">Back to dashboard</a>
        </x-page-header>

        <div class="mt-6 space-y-6">
            <section class="card p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold text-slate-900">Profile information</h2>
                <p class="mt-1 text-sm text-slate-500">Used for deliveries and account notifications.</p>

                <form method="POST" action="{{ route('profile.update') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
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

                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-primary">Save changes</button>
                    </div>
                </form>
            </section>

            @if ($user->isCustomer())
                @php($defaultAddress = $user->defaultAddress())

                <section class="card p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="font-display text-lg font-semibold text-slate-900">Delivery addresses</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $user->addresses()->count() }} saved
                                @if ($defaultAddress)
                                    &middot; default is {{ $defaultAddress->label }}
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('addresses.index') }}" class="btn-secondary">Manage addresses</a>
                    </div>

                    @if ($defaultAddress)
                        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
                            <p class="font-semibold text-slate-900">{{ $defaultAddress->label }}</p>
                            <p class="mt-1 text-slate-600">
                                {{ $defaultAddress->recipient_name }} &middot; {{ $defaultAddress->phone }}<br>
                                {{ $defaultAddress->address_line }}@if ($defaultAddress->area), {{ $defaultAddress->area }}@endif
                            </p>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-slate-500">
                            Add an address to check out faster next time.
                        </p>
                    @endif
                </section>
            @endif

            <section class="card p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold text-slate-900">Change password</h2>
                <p class="mt-1 text-sm text-slate-500">Use a long password you don't reuse anywhere else.</p>

                <form method="POST" action="{{ route('password.update') }}" class="mt-5 grid gap-4 sm:grid-cols-3">
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

                    <div class="sm:col-span-3">
                        <button type="submit" class="btn-primary">Update password</button>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-rose-200 bg-rose-50 p-5 sm:p-6">
                <h2 class="font-display text-lg font-semibold text-rose-900">Delete account</h2>
                <p class="mt-1 text-sm text-rose-700">
                    This permanently removes your account. Orders you placed stay in the system for farmers' records.
                </p>

                <form id="delete-account-form" method="POST" action="{{ route('profile.destroy') }}" class="mt-4 flex flex-wrap items-end gap-3">
                    @csrf
                    @method('delete')

                    <div class="w-full sm:w-64">
                        <label for="delete_password" class="label !text-rose-900">Confirm with your password</label>
                        <input id="delete_password" name="password" type="password" autocomplete="current-password"
                               class="input mt-1 border-rose-300 focus:border-rose-500 focus:ring-rose-500">
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
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-rose-50 text-rose-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="font-display text-lg font-semibold text-slate-900">Delete your account?</h2>
                                <p class="mt-1 text-sm text-slate-600">
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
