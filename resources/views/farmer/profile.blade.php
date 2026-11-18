<x-app-layout title="Storefront & payouts">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <p class="eyebrow mb-2">The shopfront</p>
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
                <section class="card overflow-hidden">
                    <div class="card-head">
                        <div>
                            <p class="eyebrow">On the storefront</p>
                            <h2 class="font-display text-xl font-semibold text-ink">Public storefront</h2>
                        </div>
                    </div>

                    <div class="space-y-5 p-5 sm:p-6">
                        <p class="text-sm leading-relaxed text-ink-soft">Shown to shoppers on your storefront page and product listings.</p>

                        <div>
                            <label for="farm_name" class="label">Farm or brand name</label>
                            <input id="farm_name" name="farm_name" type="text" value="{{ old('farm_name', $profile?->farm_name) }}"
                                   class="input mt-1" placeholder="e.g. Rafiq's Organic Farm">
                            @error('farm_name') <p class="field-error">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs leading-relaxed text-ink-soft">Leave blank to use your name.</p>
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
                    </div>
                </section>

                <section class="card overflow-hidden">
                    <div class="card-head">
                        <div>
                            <p class="eyebrow">Behind the counter</p>
                            <h2 class="font-display text-xl font-semibold text-ink">Payout details</h2>
                        </div>
                    </div>

                    <div class="space-y-5 p-5 sm:p-6">
                        <p class="text-sm leading-relaxed text-ink-soft">Only visible to you and the platform admin.</p>

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
                    </div>
                </section>

                <div class="border-t border-dashed border-line-dark pt-5">
                    <button type="submit" class="btn-primary">Save changes</button>
                </div>
            </div>

            <aside class="space-y-4">
                <div class="card overflow-hidden">
                    <div class="card-head">
                        <div>
                            <p class="eyebrow">Account holder</p>
                            <h2 class="font-display text-lg font-semibold text-ink">Account</h2>
                        </div>
                    </div>
                    <dl class="divide-y divide-dashed divide-line-dark/60 px-5 text-sm">
                        <div class="py-3">
                            <dt class="label">Name</dt>
                            <dd class="mt-1 font-medium text-ink">{{ $user->name }}</dd>
                        </div>
                        <div class="py-3">
                            <dt class="label">Email</dt>
                            <dd class="mt-1 text-ink-soft">{{ $user->email }}</dd>
                        </div>
                        <div class="py-3">
                            <dt class="label">Mobile</dt>
                            <dd class="mt-1 text-ink-soft">{{ $user->phone }}</dd>
                        </div>
                    </dl>
                    <div class="border-t border-dashed border-line-dark px-5 py-3">
                        <a href="{{ route('profile.edit') }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 hover:underline">
                            Edit personal details &rarr;
                        </a>
                    </div>
                </div>

                <div class="card-note">
                    <p class="eyebrow">Payout note</p>
                    <p class="mt-2 text-sm leading-relaxed text-ink-soft">
                        Payouts are calculated from delivered order items. The platform keeps the {{ (int) (config('marketplace.platform_fee') * 100) }}% customer fee and never deducts it from your earnings.
                    </p>
                </div>
            </aside>
        </form>
    </div>
</x-app-layout>
