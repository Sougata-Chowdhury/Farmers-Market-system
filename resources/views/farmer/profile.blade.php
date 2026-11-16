<x-app-layout title="Storefront & payouts">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <x-page-header title="Storefront & payouts" subtitle="How customers see your farm, and where your earnings are sent.">
            <a href="{{ route('farmer.dashboard') }}" class="btn-secondary">Back to dashboard</a>
            @if ($user->slug)
                <a href="{{ route('farmers.show', $user) }}" class="btn-primary" target="_blank" rel="noopener">
                    View public storefront
                </a>
            @endif
        </x-page-header>

        <form method="POST" action="{{ route('farmer.profile.update') }}" class="mt-6 grid gap-6 lg:grid-cols-[1.2fr_0.8fr] lg:items-start">
            @csrf
            @method('PATCH')

            <div class="space-y-6">
                <section class="card space-y-5 p-5 sm:p-6">
                    <div>
                        <h2 class="font-display text-lg font-semibold text-slate-900">Public storefront</h2>
                        <p class="mt-1 text-sm text-slate-500">Shown to shoppers on your storefront page and product listings.</p>
                    </div>

                    <div>
                        <label for="farm_name" class="label">Farm or brand name</label>
                        <input id="farm_name" name="farm_name" type="text" value="{{ old('farm_name', $profile?->farm_name) }}"
                               class="input mt-1" placeholder="e.g. Rafiq's Organic Farm">
                        @error('farm_name') <p class="field-error">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-slate-500">Leave blank to use your name.</p>
                    </div>

                    <div>
                        <label for="location" class="label">Farm location</label>
                        <input id="location" name="location" type="text" value="{{ old('location', $profile?->location) }}"
                               class="input mt-1" placeholder="e.g. Savar, Dhaka">
                        @error('location') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="bio" class="label">About your farm</label>
                        <textarea id="bio" name="bio" rows="4" class="input mt-1"
                                  placeholder="What do you grow, and how? This appears on your public storefront.">{{ old('bio', $profile?->bio) }}</textarea>
                        @error('bio') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </section>

                <section class="card space-y-5 p-5 sm:p-6">
                    <div>
                        <h2 class="font-display text-lg font-semibold text-slate-900">Payout details</h2>
                        <p class="mt-1 text-sm text-slate-500">Only visible to you and the platform admin.</p>
                    </div>

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
                </section>

                <button type="submit" class="btn-primary">Save changes</button>
            </div>

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
        </form>
    </div>
</x-app-layout>
