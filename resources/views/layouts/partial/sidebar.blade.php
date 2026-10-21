{{-- Fixed desktop navigation sidebar --}}
<aside class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col border-r border-white/50 bg-white/50 px-5 py-6 backdrop-blur-2xl lg:flex dark:border-white/10 dark:bg-black/30">
    <a href="{{ route('home') }}" class="mb-8 flex items-center gap-3 px-2" title="LaraTools">
        <span class="flex size-11 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-xl text-white shadow-lg shadow-violet-600/40">
            <i class="bi bi-hexagon-fill"></i>
        </span>
        <span class="heading text-2xl">Lara<span class="text-gradient">Tools</span></span>
    </a>

    {{-- Opens the global search overlay; Ctrl/Cmd+K does the same (modules/search.js) --}}
    <button type="button" data-search-open
            class="mb-6 flex items-center gap-2.5 rounded-xl border border-slate-900/10 bg-white/60 px-3.5 py-2.5 text-sm font-bold text-slate-500 transition-colors hover:border-violet-500/40 hover:text-slate-700 dark:border-white/10 dark:bg-white/5 dark:text-slate-400 dark:hover:border-fuchsia-400/40 dark:hover:text-slate-200">
        <i class="bi bi-search"></i>
        <span>Search…</span>
        <kbd class="ms-auto rounded-md border border-slate-900/10 bg-white/70 px-1.5 py-0.5 font-sans text-[10px] dark:border-white/10 dark:bg-white/10">Ctrl K</kbd>
    </button>

    <nav class="flex flex-col gap-1.5">
        <x-nav-link name="home" icon="bi-grid-1x2-fill" label="Dashboard" exact/>
        <x-nav-link name="notes.index" icon="bi-journal-text" label="Notes"/>
        <x-nav-link name="todos.index" icon="bi-list-check" label="Todo Lists"/>
        <x-nav-link name="calendar.index" icon="bi-calendar-event-fill" label="Calendar"/>
        <x-nav-link name="music.index" icon="bi-music-note-beamed" label="Music"/>
        <x-nav-link name="calculator" icon="bi-calculator-fill" label="Calculator" exact/>
        <x-nav-link name="converter" icon="bi-arrow-left-right" label="Converter" exact/>
        <x-nav-link name="clock" icon="bi-clock-fill" label="Clock" exact/>
        <x-nav-link name="weather.index" icon="bi-cloud-sun-fill" label="Weather"/>
    </nav>

    <div class="mt-auto flex flex-col gap-1.5 border-t border-slate-900/10 pt-4 dark:border-white/10">
        <x-nav-link name="bin.index" icon="bi-trash3" label="Recycle Bin"/>
        <x-nav-link name="settings" icon="bi-gear" label="Settings"/>
        <x-nav-link name="about" icon="bi-info-circle-fill" label="About"/>

        {{-- Theme quick-switcher; choice persisted by modules/theme.js --}}
        <div class="dropdown relative mt-3">
            <select name="appearance" data-dropdown aria-label="Theme" class="sr-only">
                <option value="light">Light</option>
                <option value="dark">Dark</option>
                <option value="system">System</option>
            </select>
        </div>

        <p class="mt-2 px-2 text-[11px] font-bold text-slate-400 dark:text-slate-500">
            v{{ config('app.version') }}
        </p>
    </div>
</aside>
