<x-guest-layout>
    <p class="eyebrow">A little help</p>
    <h1 class="mt-3 font-display text-[2rem] font-semibold leading-tight text-ink">Forgot your password?</h1>
    <div class="mt-2 text-sm leading-relaxed text-ink-soft">
        {{ __('No trouble. Tell us the email you signed up with and we will send a reset link along.') }}
    </div>

    <x-auth-session-status class="mb-4 mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-6">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1.5 block w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-5 flex items-center justify-between gap-3">
            <a href="{{ route('login') }}" class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft hover:text-ink">Back to log in</a>
            <x-primary-button>
                {{ __('Email reset link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
