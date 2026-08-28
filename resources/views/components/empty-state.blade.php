{{-- Friendly empty state used by every list page --}}
@props([
    'icon' => 'bi-inbox-fill',
    'title' => 'Nothing here yet',
])

<div class="glass mx-auto flex max-w-md flex-col items-center gap-4 p-10 text-center animate-pop-in">
    <span class="flex size-20 items-center justify-center rounded-3xl bg-gradient-to-br from-violet-500/15 to-fuchsia-500/15 text-4xl text-violet-500 dark:text-fuchsia-300">
        <i class="bi {{ $icon }}"></i>
    </span>

    <h3 class="heading text-xl">{{ $title }}</h3>

    @isset($description)
        <p class="-mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @endisset

    @isset($action)
        {{ $action }}
    @endisset
</div>
