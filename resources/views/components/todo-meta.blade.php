{{-- Priority badge and due-date pill for a todo list --}}
@props(['todo'])

@php
    $progress = $todo->progress();
    $allDone = $progress['total'] > 0 && $progress['completed'] === $progress['total'];

    $priority = $todo->priority;

    $priorityTones = [
        'low' => 'bg-sky-500/15 text-sky-700 dark:text-sky-300',
        'medium' => 'bg-amber-500/15 text-amber-700 dark:text-amber-300',
        'high' => 'bg-rose-500/15 text-rose-700 dark:text-rose-300',
    ];

    $dueTone = match (true) {
        $todo->isOverdue() => 'bg-rose-500/15 text-rose-700 dark:text-rose-300',
        $allDone => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300',
        default => 'bg-slate-900/10 text-slate-600 dark:bg-white/10 dark:text-slate-300',
    };

    $dueLabel = $todo->due_at ? match (true) {
        $todo->due_at->isToday() => 'Due today',
        $todo->due_at->isTomorrow() => 'Due tomorrow',
        $todo->due_at->isYesterday() => 'Was due yesterday',
        default => 'Due '.$todo->due_at->isoFormat('MMM D'),
    } : null;
@endphp

@if ($todo->hasPriority() || $todo->due_at)
    <div class="flex flex-wrap items-center gap-1.5">
        @if ($todo->hasPriority())
            <span class="chip {{ $priorityTones[$priority] }}">
                <i class="bi {{ $priority === 'high' ? 'bi-exclamation-diamond-fill' : ($priority === 'medium' ? 'bi-arrow-up' : 'bi-arrow-down') }}"></i>
                {{ str($priority)->title() }} priority
            </span>
        @endif

        @if ($todo->due_at)
            <span class="chip {{ $dueTone }}">
                <i class="bi {{ $todo->isOverdue() ? 'bi-alarm-fill' : 'bi-calendar-event' }}"></i>
                {{ $dueLabel }}
            </span>
        @endif
    </div>
@endif
