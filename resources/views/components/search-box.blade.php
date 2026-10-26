{{-- Type-to-filter search box (debounce lives in modules/search.js).        --}}
{{-- It must sit OUTSIDE the turbo-frame it targets: replacing the frame on   --}}
{{-- every reload would also replace the input that currently has focus.      --}}
@props([
    'action',
    'frame',
    'value' => null,
    'placeholder' => 'Search…',
    'label' => 'Search',
    'params' => [],
])

{{-- Companion filters live here as hidden inputs: a frame reload rebuilds the
     query from this form alone, so anything not carried would be dropped the
     moment the user types. --}}
<form method="GET" action="{{ $action }}" data-turbo-frame="{{ $frame }}" data-search-form
      {{ $attributes->merge(['class' => 'relative w-full']) }}>
    @foreach ($params as $key => $param)
        <input type="hidden" name="{{ $key }}" value="{{ $param }}"/>
    @endforeach

    <i class="bi bi-search pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-sm text-slate-400 dark:text-slate-500"></i>

    <input type="search" name="q" value="{{ $value }}" placeholder="{{ $placeholder }}"
           aria-label="{{ $label }}" autocomplete="off" spellcheck="false" data-search-input
           class="field-input w-full !py-2.5 !pl-10 !pr-9 !text-sm"/>

    <button type="button" title="Clear search" aria-label="Clear search" data-search-clear
            @class([
                'absolute top-1/2 right-2 grid size-6 -translate-y-1/2 place-items-center rounded-full',
                'text-slate-400 transition-colors hover:bg-slate-900/10 hover:text-slate-700',
                'dark:text-slate-500 dark:hover:bg-white/10 dark:hover:text-slate-200',
                'invisible' => blank($value),
            ])>
        <i class="bi bi-x-lg text-xs"></i>
    </button>
</form>
