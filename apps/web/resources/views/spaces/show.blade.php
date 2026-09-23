@extends('layouts.app', ['title' => $space->name])

@section('content')
    <div class="flex flex-col gap-3">
        <a href="{{ route('spaces.index') }}" class="text-sm text-muted underline decoration-line underline-offset-4 hover:text-ink hover:decoration-accent dark:text-stone-400 dark:hover:text-stone-100">
            All spaces
        </a>
        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-semibold tracking-tight">{{ $space->name }}</h1>
            <p class="max-w-xl text-base leading-7 text-muted dark:text-stone-400">
                Files and notes in this space are yours. Your agents read and write the same files.
            </p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <section class="flex flex-col gap-4 rounded-3xl border border-line bg-white p-5 shadow-sm dark:border-stone-800 dark:bg-stone-900" aria-labelledby="upload-heading">
            <div class="flex flex-col gap-1">
                <h2 id="upload-heading" class="text-base font-medium">Upload a file</h2>
                <p class="text-sm leading-6 text-muted dark:text-stone-400">
                    Up to {{ \App\Spaces\UploadLimit::label() }}. Saving the same name replaces the file.
                </p>
            </div>
            <form method="POST" action="{{ route('spaces.files.store', $space->slug) }}" enctype="multipart/form-data" class="flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-2">
                    <label for="file" class="text-sm font-medium">File</label>
                    <input
                        id="file"
                        name="file"
                        type="file"
                        required
                        class="w-full text-sm file:me-3 file:rounded-full file:border-0 file:bg-accent-soft file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-accent dark:file:bg-emerald-400/10 dark:file:text-emerald-200"
                        @error('file') aria-invalid="true" aria-describedby="form-errors" @enderror
                    >
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-full bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent dark:bg-emerald-400 dark:text-stone-950 dark:hover:bg-emerald-300">
                        Upload
                    </button>
                </div>
            </form>
        </section>

        <section class="flex flex-col gap-4 rounded-3xl border border-line bg-white p-5 shadow-sm dark:border-stone-800 dark:bg-stone-900" aria-labelledby="note-heading">
            <div class="flex flex-col gap-1">
                <h2 id="note-heading" class="text-base font-medium">Write a note</h2>
                <p class="text-sm leading-6 text-muted dark:text-stone-400">
                    A note is a text file your agents can open by name.
                </p>
            </div>
            <form method="POST" action="{{ route('spaces.notes.store', $space->slug) }}" class="flex flex-col gap-4">
                @csrf
                <div class="flex flex-col gap-2">
                    <label for="note-name" class="text-sm font-medium">Name</label>
                    <input
                        id="note-name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        maxlength="180"
                        placeholder="Meeting notes"
                        class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-ink shadow-sm outline-none placeholder:text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100 dark:placeholder:text-stone-500"
                        @error('name') aria-invalid="true" aria-describedby="form-errors" @enderror
                    >
                </div>
                <div class="flex flex-col gap-2">
                    <label for="note-body" class="text-sm font-medium">Note</label>
                    <textarea
                        id="note-body"
                        name="body"
                        rows="5"
                        required
                        class="w-full rounded-xl border border-line bg-white px-3 py-2 text-sm text-ink shadow-sm outline-none placeholder:text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 dark:border-stone-700 dark:bg-stone-950 dark:text-stone-100 dark:placeholder:text-stone-500"
                        @error('body') aria-invalid="true" aria-describedby="form-errors" @enderror
                    >{{ old('body') }}</textarea>
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-full bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent dark:bg-emerald-400 dark:text-stone-950 dark:hover:bg-emerald-300">
                        Save note
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="flex flex-col gap-4" aria-labelledby="file-list-heading">
        <h2 id="file-list-heading" class="text-sm font-medium text-muted dark:text-stone-400">Files</h2>

        @if ($files->isEmpty())
            <x-empty-state title="Nothing in this space yet">
                Upload a file or write a note. An agent can also put a file here through the API, and it will show up in this list.
            </x-empty-state>
        @else
            <ul class="flex flex-col divide-y divide-line rounded-3xl border border-line bg-white px-4 dark:divide-stone-800 dark:border-stone-800 dark:bg-stone-900">
                @foreach ($files as $file)
                    <li class="flex flex-col gap-1 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('spaces.files.show', [$space->slug, $file->name]) }}" class="font-medium underline decoration-line underline-offset-4 hover:decoration-accent">
                            {{ $file->name }}
                        </a>
                        <p class="flex flex-wrap gap-x-3 text-sm text-muted dark:text-stone-400">
                            <span>{{ \Illuminate\Support\Number::fileSize($file->size) }}</span>
                            <time datetime="{{ \Illuminate\Support\Carbon::createFromTimestamp($file->updatedAt)->toIso8601String() }}">
                                {{ \Illuminate\Support\Carbon::createFromTimestamp($file->updatedAt)->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                            </time>
                        </p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
