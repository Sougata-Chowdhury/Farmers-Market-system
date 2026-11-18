<x-guest-layout>
    <p class="eyebrow">One last step</p>
    <h1 class="mt-3 font-display text-[2rem] font-semibold leading-tight text-ink">Check your inbox</h1>
    <div class="mt-2 text-sm leading-relaxed text-ink-soft">
        {{ __('Thanks for signing up. Click the link we just emailed you to verify your address and open the stall.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 border border-forest-700/30 border-l-4 border-l-forest-700 bg-forest-50 px-4 py-3 text-sm font-medium text-forest-900">
            {{ __('A new verification link has been sent to the email address you provided.') }}
        </div>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-dashed border-line-dark pt-5">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                {{ __('Resend email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="font-mono text-[10px] font-bold uppercase tracking-label text-ink-soft hover:text-ink focus:outline-none">
                {{ __('Log out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
