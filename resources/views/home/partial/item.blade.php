{{-- Launcher tile for a single tool --}}
@php($delay = $delay ?? 0)

<a href="{{ $route }}"
   class="glass glass-hover group flex flex-col items-center gap-4 p-6 animate-fade-up sm:gap-6 sm:p-8"
   style="animation-delay: {{ $delay }}ms">
    <span class="flex size-16 items-center justify-center rounded-2xl bg-gradient-to-br text-3xl text-white shadow-lg transition-all duration-300 group-hover:-rotate-6 group-hover:scale-110 sm:size-20 sm:text-4xl {{ $gradient }} {{ $shadow }}">
        <i class="bi {{ $icon }} transition-colors group-hover:hidden"></i>
        <i class="bi {{ $iconFill }} hidden transition-colors group-hover:block"></i>
    </span>

    <span class="heading text-center text-base sm:text-lg">
        {{ $label }}
    </span>
</a>
