<x-guest-layout>
    <div class="flex items-center justify-between gap-3">
        <p class="eyebrow">Returning customer</p>
        <span class="stamp stamp-green !px-2 !py-0.5 !text-[8px]">Market open</span>
    </div>

    <h1 class="mt-3 font-display text-[2rem] font-semibold leading-tight text-ink">Welcome back to the stall</h1>
    <p class="mt-2 text-sm leading-relaxed text-ink-soft">Sign in to shop the market or tend to your farm.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="label">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   autocomplete="username" class="input mt-1.5" placeholder="you@example.com">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="input mt-1.5" placeholder="••••••••">
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between gap-3 border-b border-dashed border-line-dark pb-4">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-ink-soft">
                <input id="remember_me" name="remember" type="checkbox"
                       class="rounded-sm border-line-dark text-forest-700 shadow-sm focus:ring-forest-600">
                Keep me signed in
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-clay-600 underline-offset-4 hover:underline">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="btn-primary w-full">Log in</button>
    </form>

    <p class="mt-5 text-center text-sm text-ink-soft">
        New around here?
        <a href="{{ route('register') }}" class="link">Create an account</a>
    </p>

    <div class="mt-6 border-t border-dashed border-line-dark pt-5">
        <p class="eyebrow">Demo accounts</p>
        <ul class="mt-3 space-y-1.5 font-mono text-[11px] text-ink-soft">
            <li class="flex justify-between gap-3"><span>Customer</span><span class="text-ink">customer@farmersmarket.test</span></li>
            <li class="flex justify-between gap-3"><span>Farmer</span><span class="text-ink">farmer@farmersmarket.test</span></li>
            <li class="flex justify-between gap-3"><span>Admin</span><span class="text-ink">admin@farmersmarket.test</span></li>
        </ul>
        <p class="mt-2 font-mono text-[10px] uppercase tracking-label text-ink-faint">
            Password for all: <span class="text-clay-600">password</span>
        </p>
    </div>
</x-guest-layout>
