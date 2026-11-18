<x-guest-layout>
    <p class="eyebrow">Just to be sure</p>
    <h1 class="mt-3 font-display text-[2rem] font-semibold leading-tight text-ink">Confirm your password</h1>
    <div class="mt-2 text-sm leading-relaxed text-ink-soft">
        {{ __('This is a secure part of the market. Please confirm your password before we continue.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1.5 block w-full"
                          type="password"
                          name="password"
                          required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-5 flex justify-end">
            <x-primary-button>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
