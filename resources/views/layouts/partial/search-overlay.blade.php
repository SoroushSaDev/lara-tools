{{-- Global search overlay: one term across notes, todo lists and music.     --}}
{{-- Opened by the sidebar and top-bar buttons or Ctrl/Cmd+K (modules/search.js). --}}
<div class="search-overlay fixed inset-0 z-50 hidden items-start justify-center px-4 pt-[8vh] pb-6 sm:px-6"
     data-search-overlay role="dialog" aria-modal="true" aria-label="Search everything">
    <div class="absolute inset-0 bg-slate-900/45 backdrop-blur-sm" data-search-backdrop></div>

    <div class="glass relative flex max-h-[82vh] w-full max-w-xl flex-col gap-3 overflow-hidden p-4 animate-pop-in">
        <x-search-box :action="route('search')" frame="global-search-results"
                      placeholder="Search notes, todo lists and music…" label="Search everything"/>

        <div class="min-h-0 flex-1 overflow-y-auto">
            @include('search.partial.results', ['term' => null, 'query' => null, 'modules' => [], 'count' => 0])
        </div>

        <p class="flex items-center justify-center gap-1.5 text-[11px] font-bold text-slate-400 dark:text-slate-500">
            <kbd class="rounded-md border border-slate-900/10 bg-white/60 px-1.5 py-0.5 font-sans text-[10px] dark:border-white/10 dark:bg-white/10">Esc</kbd>
            <span>to close</span>
            <span class="opacity-50">·</span>
            <span>results link straight into each module</span>
        </p>
    </div>
</div>
