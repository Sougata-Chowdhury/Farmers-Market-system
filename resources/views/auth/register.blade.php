<x-guest-layout>
    <p class="eyebrow">New at the market</p>
    <h1 class="mt-3 font-display text-[2rem] font-semibold leading-tight text-ink">Pull up a chair</h1>
    <p class="mt-2 text-sm leading-relaxed text-ink-soft">Shop the harvest, or open a stall as a farmer. Both take a minute.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <fieldset>
            <legend class="label">I'm here to</legend>
            <div class="mt-2 grid grid-cols-2 gap-3">
                @foreach (['customer' => ['Shop the market', 'Order from local farms'], 'farmer' => ['Sell my harvest', 'Open a farm stall']] as $value => [$label, $hint])
                    <label class="relative flex cursor-pointer border border-line bg-white p-3 transition hover:border-forest-500 has-[:checked]:border-forest-700 has-[:checked]:bg-forest-50/70 has-[:checked]:shadow-md">
                        <input type="radio" name="role" value="{{ $value }}" class="sr-only"
                               @checked(old('role', 'customer') === $value)>
                        <span>
                            <span class="block font-display text-[15px] font-semibold text-ink">{{ $label }}</span>
                            <span class="mt-0.5 block font-mono text-[9px] uppercase tracking-label text-ink-faint">{{ $hint }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('role') <p class="field-error">{{ $message }}</p> @enderror
        </fieldset>

        <div>
            <label for="name" class="label">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                   autocomplete="name" class="input mt-1.5" placeholder="As it should appear on the order">
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="label">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                   autocomplete="username" class="input mt-1.5" placeholder="you@example.com">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="label">Mobile number</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required
                   autocomplete="tel" class="input mt-1.5" placeholder="01XXXXXXXXX">
            @error('phone') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="address" class="label">Address</label>
            <input id="address" name="address" type="text" value="{{ old('address') }}" required
                   autocomplete="street-address" class="input mt-1.5" placeholder="Area, district">
            @error('address') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="input mt-1.5">
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       autocomplete="new-password" class="input mt-1.5">
            </div>
        </div>

        <button type="submit" class="btn-primary w-full">Create account</button>
    </form>

    <p class="mt-5 text-center text-sm text-ink-soft">
        Already have a stall?
        <a href="{{ route('login') }}" class="link">Log in</a>
    </p>
</x-guest-layout>
