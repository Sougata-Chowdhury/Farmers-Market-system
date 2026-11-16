<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#047857">

        <title>{{ isset($title) ? $title.' · '.config('app.name') : config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|fraunces:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full bg-slate-50 font-sans text-slate-900 antialiased">
        <div class="flex min-h-screen flex-col">
            @include('layouts.navigation')

            <main class="flex-1">
                <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                    @include('partials.flash')
                </div>

                {{ $slot }}
            </main>

            <footer class="mt-16 border-t border-slate-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-10 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Farmers Market</p>
                        <p class="mt-1 max-w-md text-sm text-slate-500">
                            A direct-to-consumer marketplace connecting local farmers with households in Bangladesh.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-500">
                        <a href="{{ route('home') }}" class="hover:text-emerald-700">Browse products</a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="hover:text-emerald-700">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="hover:text-emerald-700">Log in</a>
                            <a href="{{ route('register') }}" class="hover:text-emerald-700">Create account</a>
                        @endauth
                        <span class="text-slate-400">&copy; {{ now()->year }} Farmers Market</span>
                    </div>
                </div>
            </footer>
        </div>

        @stack('scripts')
    </body>
</html>
