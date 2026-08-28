@props([
    'name' => '',
    'icon' => '',
    'label' => '',
    'compact' => false,
])

@php
    // Highlight when any route of the same feature is active (e.g. notes.create).
    $base = str_contains($name, '.') ? str($name)->before('.') : $name;
    $isActive = request()->routeIs($base) || request()->routeIs($base.'.*');
@endphp

<a href="{{ route($name) }}"
   {{ $attributes->class([
        'group relative flex flex-1 items-center gap-3 rounded-xl font-bold transition-all duration-200',
        'px-4 py-2.5 text-sm' => ! $compact,
        'flex-col gap-1 px-1 py-1.5 text-[10px]' => $compact,
        'bg-gradient-to-r from-violet-600/10 to-fuchsia-600/10 text-violet-700 dark:text-fuchsia-300' => $isActive && ! $compact,
        'text-slate-500 hover:bg-white/60 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-white' => ! $isActive,
    ]) }}>
    @if ($compact)
        @if ($isActive)
            <span class="absolute -top-1.5 h-1 w-8 rounded-full bg-gradient-to-r from-violet-500 to-fuchsia-500"></span>
        @endif
        <i class="bi {{ $icon }} text-xl transition-transform duration-200 group-hover:scale-110"></i>
        <span class="truncate">{{ $label }}</span>
    @else
        <i class="bi {{ $icon }} text-lg transition-transform duration-200 group-hover:scale-110
            {{ $isActive ? 'text-gradient' : '' }}"></i>
        <span>{{ $label }}</span>
    @endif
</a>
