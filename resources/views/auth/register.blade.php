<x-guest-layout>
    <h1 class="font-display text-2xl font-semibold tracking-tight text-slate-900">Create your account</h1>
    <p class="mt-1 text-sm text-slate-500">Shop fresh produce, or open a storefront as a farmer.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <fieldset>
            <legend class="label">I want to</legend>
            <div class="mt-2 grid grid-cols-2 gap-2">
                @foreach (['customer' => ['Shop produce', 'Order from local farms'], 'farmer' => ['Sell produce', 'Open a storefront']] as $value => [$label, $hint])
                    <label class="relative flex cursor-pointer rounded-xl border border-slate-200 bg-white p-3 transition hover:border-emerald-300 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/60 has-[:checked]:ring-1 has-[:checked]:ring-emerald-600">
                        <input type="radio" name="role" value="{{ $value }}" class="sr-only"
                               @checked(old('role', 'customer') === $value)>
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">{{ $label }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500">{{ $hint }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('role') <p class="field-error">{{ $message }}</p> @enderror
        </fieldset>

        <div>
            <label for="name" class="label">Full name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                   autocomplete="name" class="input mt-1">
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="label">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                   autocomplete="username" class="input mt-1" placeholder="you@example.com">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="phone" class="label">Mobile number</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required
                   autocomplete="tel" class="input mt-1" placeholder="01XXXXXXXXX">
            @error('phone') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="address" class="label">Address</label>
            <input id="address" name="address" type="text" value="{{ old('address') }}" required
                   autocomplete="street-address" class="input mt-1" placeholder="Area, district">
            @error('address') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="input mt-1">
                @error('password') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       autocomplete="new-password" class="input mt-1">
            </div>
        </div>

        <button type="submit" class="btn-primary w-full">Create account</button>
    </form>

    <p class="mt-5 text-center text-sm text-slate-500">
        Already registered?
        <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:underline">Log in</a>
    </p>
</x-guest-layout>
