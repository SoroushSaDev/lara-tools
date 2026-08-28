{{-- Shared event editor form (expects $action, $date and optional $event) --}}
@php($event = $event ?? null)

<form id="event-form" method="POST" action="{{ $action }}" class="flex flex-col gap-4 animate-fade-up">
    @csrf

    @if ($event)
        @method('PUT')
    @endif

    <div>
        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Title
        </label>

        <input type="text" name="title" value="{{ old('title', $event?->title) }}" required autocomplete="off"
               class="field-input !text-lg"
               placeholder="What is happening?"/>

        @error('title')
            <span class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</span>
        @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Date
            </label>

            <input type="date" name="starts_at_date" required
                   value="{{ old('starts_at_date', $date->format('Y-m-d')) }}"
                   class="field-input"/>

            @error('starts_at_date')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                Time
            </label>

            <input type="time" name="starts_at_time" required
                   value="{{ old('starts_at_time', $date->format('H:i')) }}"
                   class="field-input"/>

            @error('starts_at_time')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div>
        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Description
        </label>

        <textarea name="description"
                  class="field-input min-h-24 resize-y leading-relaxed"
                  placeholder="Details (optional)">{{ old('description', $event?->description) }}</textarea>
    </div>

    <div>
        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Address
        </label>

        <input type="text" name="address" value="{{ old('address', $event?->address) }}"
               class="field-input"
               placeholder="Where? (optional)"/>
    </div>
</form>
