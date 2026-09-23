@extends('layouts.app', ['title' => 'Your spaces'])

@section('content')
    <div class="flex flex-col gap-2">
        <p class="text-sm font-medium text-accent dark:text-emerald-300">Personal</p>
        <h1 class="text-3xl font-semibold tracking-tight">Your spaces</h1>
        <p class="max-w-xl text-base leading-7 text-muted dark:text-stone-400">
            Keep files and notes in one place you share with your agents. Each space is yours.
        </p>
    </div>

    <section class="rounded-3xl border border-line bg-white p-5 shadow-sm dark:border-stone-800 dark:bg-stone-900" aria-labelledby="create-space-heading">
        <form method="POST" action="{{ route('spaces.store') }}" class="flex flex-col gap-4">
            @csrf
            <div class="flex flex-col gap-2">
                <label for="name" id="create-space-heading" class="text-sm font-medium">New space</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    required
                    maxlength="80"
                    placeholder="Garden notes"
                    autocomplete="off"
                    class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-ink shadow-sm outline-none placeholder:text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100 dark:placeholder:text-stone-500"
                    @error('name') aria-invalid="true" aria-describedby="form-errors" @enderror
                >
            </div>
            <div>
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent dark:bg-emerald-400 dark:text-stone-950 dark:hover:bg-emerald-300">
                    Create space
                </button>
            </div>
        </form>
    </section>

    <section class="flex flex-col gap-4" aria-labelledby="space-list-heading">
        <h2 id="space-list-heading" class="text-sm font-medium text-muted dark:text-stone-400">Spaces</h2>

        @if ($spaces->isEmpty())
            <x-empty-state title="No spaces yet">
                A space is a private folder for the files and notes you share with your agents. Create one to get started.
            </x-empty-state>
        @else
            <ul class="flex flex-col gap-3">
                @foreach ($spaces as $space)
                    <li>
                        <a href="{{ route('spaces.show', $space->slug) }}" class="flex flex-col gap-1 rounded-2xl border border-line bg-white px-4 py-4 hover:border-accent sm:flex-row sm:items-center sm:justify-between dark:border-stone-800 dark:bg-stone-900 dark:hover:border-emerald-400">
                            <span class="font-medium">{{ $space->name }}</span>
                            <time datetime="{{ $space->createdAt }}" class="text-sm text-muted dark:text-stone-400">
                                {{ \Illuminate\Support\Carbon::parse($space->createdAt)->timezone(config('app.timezone'))->format('M j, Y') }}
                            </time>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
