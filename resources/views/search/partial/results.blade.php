{{-- Global search matches, grouped by module.
     target="_top": every hit leaves the search surface, so the frame must
     not try to render /notes/{id} inside itself. --}}
<turbo-frame id="global-search-results" target="_top">
    @if (blank($term))
        <p class="px-3 py-8 text-center text-sm font-semibold text-slate-400 dark:text-slate-500">
            <i class="bi bi-search me-2"></i>Type to search your notes, todo lists and music.
        </p>
    @elseif ($count === 0)
        <p class="px-3 py-8 text-center text-sm font-semibold text-slate-400 dark:text-slate-500">
            Nothing contains “{{ $query }}”.
        </p>
    @else
        <div class="flex flex-col gap-4">
            @foreach ($modules as $module)
                <section>
                    <h2 class="mb-1 flex items-center justify-between gap-2 px-2.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        <span>{{ $module['label'] }}</span>
                        <span>{{ $module['total'] }}</span>
                    </h2>

                    <div class="flex flex-col gap-0.5">
                        @foreach ($module['hits'] as $hit)
                            <x-search-hit :href="$hit['href']" :icon="$hit['icon']" :title="$hit['title']" :meta="$hit['meta']"/>
                        @endforeach

                        @if ($module['total'] > count($module['hits']))
                            <a href="{{ route($module['route'], ['q' => $query]) }}"
                               class="flex items-center gap-2 px-2.5 py-2 text-xs font-bold text-violet-600 transition-colors hover:text-violet-800 dark:text-fuchsia-300 dark:hover:text-fuchsia-200">
                                <i class="bi bi-list-ul"></i>
                                See all {{ $module['total'] }} in {{ $module['label'] }}
                            </a>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</turbo-frame>
