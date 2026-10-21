{{-- Todo lists, re-rendered inside their own frame while the term changes.
     target="_top" lets the list cards navigate the page instead of the frame. --}}
<turbo-frame id="todos-results" target="_top">
    @if (filled($term))
        <p class="mb-4 flex flex-wrap items-center gap-x-2.5 gap-y-1 text-sm font-bold text-slate-500 dark:text-slate-400">
            <span>
                <i class="bi bi-search me-1.5"></i>{{ $todos->count() }} of {{ $total }} {{ str('list')->plural($total) }} match “{{ $query }}”
            </span>

            <a href="{{ route('todos.index') }}" class="text-violet-600 transition-colors hover:text-violet-800 dark:text-fuchsia-300 dark:hover:text-fuchsia-200">
                Clear
            </a>
        </p>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($todos as $todo)
            @php($progress = $todo->progress())

            <a href="{{ route('todos.show', $todo) }}"
               @class([
                   'glass glass-hover group flex flex-col gap-3 p-5',
                   'animate-fade-up' => blank($term),
               ])
               style="animation-delay: {{ ($loop->index % 9) * 60 }}ms">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="heading truncate text-lg leading-snug">{{ $todo->title }}</h2>

                    <span class="chip shrink-0 {{ $progress['percent'] === 100 ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-300' : 'bg-slate-900/10 text-slate-600 dark:bg-white/10 dark:text-slate-300' }}">
                        <i class="bi {{ $progress['percent'] === 100 ? 'bi-check-circle-fill' : 'bi-list-task' }}"></i>
                        {{ $progress['completed'] }}/{{ $progress['total'] }}
                    </span>
                </div>

                @if ($todo->description)
                    <p class="line-clamp-2 text-sm font-semibold leading-relaxed text-slate-600 dark:text-slate-400">
                        {{ $todo->description }}
                    </p>
                @endif

                <x-todo-meta :todo="$todo"/>

                <div class="mt-auto space-y-1.5 pt-2">
                    <div class="progress-track">
                        <span class="progress-fill block" style="width: {{ $progress['percent'] }}%"></span>
                    </div>

                    <div class="flex items-center justify-between text-xs font-bold text-slate-400 dark:text-slate-500">
                        <span>{{ $progress['percent'] }}% complete</span>
                        <i class="bi bi-arrow-right opacity-0 transition-opacity group-hover:opacity-100"></i>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full py-6">
                @if (filled($term))
                    <x-empty-state icon="bi-search" title="No list matches that">
                        <x-slot:description>
                            Nothing in your titles, descriptions or checklist items contains “{{ $query }}”.
                        </x-slot:description>

                        <x-slot:action>
                            <a href="{{ route('todos.index') }}" class="btn-primary mt-2">
                                <i class="bi bi-x-lg"></i>Show all lists
                            </a>
                        </x-slot:action>
                    </x-empty-state>
                @else
                    <x-empty-state icon="bi-list-check" title="No todo lists yet">
                        <x-slot:description>Organise your day with a checklist.</x-slot:description>

                        <x-slot:action>
                            <a href="{{ route('todos.create') }}" class="btn-primary mt-2">
                                <i class="bi bi-plus-lg"></i>Create a list
                            </a>
                        </x-slot:action>
                    </x-empty-state>
                @endif
            </div>
        @endforelse
    </div>
</turbo-frame>
