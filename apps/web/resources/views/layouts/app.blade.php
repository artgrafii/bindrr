<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Personal spaces for the files and notes you share with your agents.">
        <title>{{ $title ?? 'Spaces' }} — bindrr</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-full flex-col bg-paper text-ink antialiased dark:bg-stone-950 dark:text-stone-100">
        <a href="#content" class="absolute start-4 top-4 -translate-y-24 rounded-full bg-white px-3 py-2 text-sm font-medium text-ink focus:translate-y-0 dark:bg-stone-900 dark:text-stone-100">
            Skip to content
        </a>

        <header class="border-b border-line bg-white/80 dark:border-stone-800 dark:bg-stone-950/80">
            <div class="mx-auto flex w-full max-w-3xl items-center justify-between gap-4 px-4 py-4">
                <a href="{{ route('spaces.index') }}" class="flex flex-col gap-0.5">
                    <span class="text-lg font-semibold tracking-tight">bindrr</span>
                    <span class="text-xs text-muted dark:text-stone-400">Personal spaces</span>
                </a>
            </div>
        </header>

        <main id="content" class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-8 px-4 py-8">
            @if (session()->isStarted() && session('status'))
                <p role="status" class="rounded-2xl border border-accent/30 bg-accent-soft px-4 py-3 text-sm text-accent dark:border-emerald-400/30 dark:bg-emerald-400/10 dark:text-emerald-200">
                    {{ session('status') }}
                </p>
            @endif

            @if (isset($errors) && $errors->any())
                <div id="form-errors" role="alert" class="rounded-2xl border border-danger/30 bg-danger-soft px-4 py-3 text-sm text-danger dark:border-red-400/30 dark:bg-red-400/10 dark:text-red-200">
                    <ul class="flex flex-col gap-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="border-t border-line dark:border-stone-800">
            <p class="mx-auto w-full max-w-3xl px-4 py-6 text-sm text-muted dark:text-stone-400">
                Your files stay in your spaces, where your agents can read and write them too.
            </p>
        </footer>
    </body>
</html>
