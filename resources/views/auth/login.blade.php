<x-guest-layout>
    <h1 class="font-display text-2xl font-semibold tracking-tight text-slate-900">Welcome back</h1>
    <p class="mt-1 text-sm text-slate-500">Sign in to shop the market or manage your farm.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="label">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   autocomplete="username" class="input mt-1" placeholder="you@example.com">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="label">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="input mt-1" placeholder="••••••••">
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input id="remember_me" name="remember" type="checkbox"
                       class="rounded border-slate-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                Remember me
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-emerald-700 hover:underline">
                    Forgot password?
                </a>
            @endif
        </div>

        <button type="submit" class="btn-primary w-full">Log in</button>
    </form>

    <p class="mt-5 text-center text-sm text-slate-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-semibold text-emerald-700 hover:underline">Create one</a>
    </p>

    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Demo accounts</p>
        <ul class="mt-2 space-y-1 text-xs text-slate-600">
            <li class="flex justify-between gap-3"><span>Customer</span><span class="font-mono">customer@farmersmarket.test</span></li>
            <li class="flex justify-between gap-3"><span>Farmer</span><span class="font-mono">farmer@farmersmarket.test</span></li>
            <li class="flex justify-between gap-3"><span>Admin</span><span class="font-mono">admin@farmersmarket.test</span></li>
        </ul>
        <p class="mt-2 text-xs text-slate-500">Password for all: <span class="font-mono font-semibold">password</span></p>
    </div>
</x-guest-layout>
