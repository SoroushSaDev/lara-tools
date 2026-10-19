@props([
    'back' => null,
    'backLabel' => 'Back',
    'subtitle' => null,
])

{{-- Consistent page heading: optional back button, title block and action buttons --}}
<div class="mb-6 flex flex-wrap items-center gap-x-4 gap-y-3 sm:mb-8">
    @if ($back)
        <a href="{{ $back }}" class="icon-btn" title="{{ $backLabel }}">
            <i class="bi bi-chevron-left"></i>
        </a>
    @endif

    <div class="min-w-0 flex-1 animate-fade-up">
        <h1 class="heading truncate text-2xl sm:text-3xl">
            {{ $title }}
        </h1>

        @if ($subtitle)
            <p class="mt-0.5 truncate text-sm font-semibold text-slate-500 dark:text-slate-400">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    <div class="flex items-center gap-2.5">
        @isset($actions)
            {{ $actions }}
        @endisset
    </div>
</div>
