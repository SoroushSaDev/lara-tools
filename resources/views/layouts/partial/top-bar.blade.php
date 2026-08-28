{{-- Sticky mobile top bar with brand and quick actions --}}
<header class="sticky top-0 z-40 border-b border-white/50 bg-white/60 backdrop-blur-2xl lg:hidden dark:border-white/10 dark:bg-black/40">
    <div class="flex items-center justify-between px-4 py-3">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <span class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-white shadow-md shadow-violet-600/40">
                <i class="bi bi-hexagon-fill"></i>
            </span>
            <span class="heading text-lg">Lara<span class="text-gradient">Tools</span></span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('bin.index') }}" class="icon-btn size-9" title="Recycle Bin">
                <i class="bi bi-trash3"></i>
            </a>
            <a href="{{ route('settings') }}" class="icon-btn size-9" title="Settings">
                <i class="bi bi-gear"></i>
            </a>
            <button type="button" class="icon-btn size-9" data-theme-toggle title="Toggle theme">
                <i class="bi bi-moon-stars"></i>
            </button>
        </div>
    </div>
</header>
