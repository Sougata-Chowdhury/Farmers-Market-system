<x-app-layout title="Payout details">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Payout details" subtitle="Where your earnings are sent once an order is delivered.">
            <a href="{{ route('farmer.dashboard') }}" class="btn-secondary">Back to dashboard</a>
        </x-page-header>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_0.8fr] lg:items-start">
            <form method="POST" action="{{ route('farmer.profile.update') }}" class="card space-y-5 p-5 sm:p-6">
                @csrf
                @method('PATCH')

                <div>
                    <label for="bank_name" class="label">Bank name</label>
                    <input id="bank_name" name="bank_name" type="text" value="{{ old('bank_name', $profile?->bank_name) }}"
                           required class="input mt-1" placeholder="e.g. BRAC Bank">
                    @error('bank_name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="branch" class="label">Branch or routing number</label>
                    <input id="branch" name="branch" type="text" value="{{ old('branch', $profile?->branch) }}"
                           required class="input mt-1" placeholder="e.g. Savar Branch">
                    @error('branch') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="account_no" class="label">Account number</label>
                    <input id="account_no" name="account_no" type="text" value="{{ old('account_no', $profile?->account_no) }}"
                           required class="input mt-1">
                    @error('account_no') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn-primary">Save payout details</button>
            </form>

            <aside class="space-y-4">
                <div class="card p-5">
                    <h2 class="font-display text-lg font-semibold text-slate-900">Account</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Name</dt>
                            <dd class="font-medium text-slate-900">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Email</dt>
                            <dd class="text-slate-700">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-slate-400">Mobile</dt>
                            <dd class="text-slate-700">{{ $user->phone }}</dd>
                        </div>
                    </dl>
                    <a href="{{ route('profile.edit') }}" class="mt-4 inline-flex text-sm font-medium text-emerald-700 hover:underline">
                        Edit personal details
                    </a>
                </div>

                <div class="card p-5 text-sm text-slate-500">
                    Payouts are calculated from delivered order items. The platform keeps the {{ (int) (config('marketplace.platform_fee') * 100) }}% customer fee and never deducts it from your earnings.
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
