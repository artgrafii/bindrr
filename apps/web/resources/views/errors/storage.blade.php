@extends('layouts.app', ['title' => 'Storage unavailable'])

@section('content')
    <div class="flex flex-col gap-4">
        <p class="text-sm font-medium text-danger dark:text-red-300">Storage</p>
        <h1 class="text-3xl font-semibold tracking-tight">We couldn't reach your files</h1>
        <p class="max-w-xl text-base leading-7 text-muted dark:text-stone-400">
            The space storage didn't respond. Check the S3 settings, then try again.
        </p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ url()->current() }}" class="inline-flex items-center justify-center rounded-full bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent dark:bg-emerald-400 dark:text-stone-950 dark:hover:bg-emerald-300">
                Try again
            </a>
            <a href="{{ route('spaces.index') }}" class="inline-flex items-center justify-center rounded-full border border-line px-4 py-2 text-sm font-medium hover:border-accent dark:border-stone-700 dark:hover:border-emerald-400">
                Back to your spaces
            </a>
        </div>
    </div>
@endsection
