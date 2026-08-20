@props(['title' => null])
<!DOCTYPE html>
<html lang="en" class="h-full bg-[var(--color-bg)] antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' — Vocab' : 'Vocab — IELTS Vocabulary Builder' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans text-[var(--color-foreground)]">
    <div class="min-h-full">
        <x-nav.topbar />

        <div class="mx-auto flex max-w-6xl gap-6 px-4 pb-24 pt-6 sm:px-6 lg:pb-10 lg:pt-8">
            <x-nav.sidebar class="hidden lg:block" />

            <main class="min-w-0 flex-1">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                        x-transition
                        class="mb-4 flex items-center gap-2 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-[var(--color-success)]">
                        <x-icon name="check" class="size-4 shrink-0" />
                        {{ session('success') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>

        <x-nav.bottom class="lg:hidden" />
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
