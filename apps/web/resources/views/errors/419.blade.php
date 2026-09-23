@extends('layouts.app', ['title' => 'Page expired'])

@section('content')
    <div class="flex flex-col gap-4">
        <p class="text-sm font-medium text-accent dark:text-emerald-300">419</p>
        <h1 class="text-3xl font-semibold tracking-tight">This page expired</h1>
        <p class="max-w-xl text-base leading-7 text-muted dark:text-stone-400">
            Refresh the page and try again. Your space is still there.
        </p>
        <div>
            <a href="{{ route('spaces.index') }}" class="inline-flex items-center justify-center rounded-full bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent dark:bg-emerald-400 dark:text-stone-950 dark:hover:bg-emerald-300">
                Back to your spaces
            </a>
        </div>
    </div>
@endsection
