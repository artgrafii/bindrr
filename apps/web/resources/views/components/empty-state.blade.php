@props(['title'])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center gap-2 rounded-3xl border border-dashed border-line bg-white/70 px-6 py-10 text-center dark:border-stone-700 dark:bg-stone-900/60']) }}>
    <h2 class="text-lg font-medium text-ink dark:text-stone-100">{{ $title }}</h2>
    <div class="max-w-md text-sm leading-6 text-muted dark:text-stone-400">
        {{ $slot }}
    </div>
</div>
