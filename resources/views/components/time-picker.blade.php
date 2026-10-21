{{-- Reusable time-of-day picker, built from the same custom dropdown the rest of
     the app uses. modules/time-picker.js keeps the parts and the hidden value in
     sync, so consumers read `#${id}` as a plain "HH:MM" string.

     The value is deliberately blank unless `$value` carries a time: the server's
     clock is UTC while alarms are local, so pre-filling "now" would be wrong. --}}
@props([
    'id' => null,
    'name' => null,
    'value' => null,
    'format' => '24h',
    'label' => 'Time',
    'class' => '',
])

@php
    $use24 = $format !== '12h';
    [$hour24, $minute] = preg_match('/^(\d{1,2}):(\d{2})$/', (string) $value, $matches)
        ? [(int) $matches[1], (int) $matches[2]]
        : [null, null];
    $hour = $hour24 === null ? null : ($use24 ? $hour24 : (($hour24 % 12) ?: 12));
    $meridiem = $hour24 === null ? 'am' : ($hour24 >= 12 ? 'pm' : 'am');
    $hours = $use24 ? range(0, 23) : range(1, 12);
    // A time the caller wrote loosely ("7:30") is stored padded, which is what
    // the alarm rows and the module compare.
    $stored = $hour24 === null ? $value : sprintf('%02d:%02d', $hour24, $minute);
@endphp

<div data-time-picker data-format="{{ $use24 ? '24h' : '12h' }}"
     role="group" @if ($label) aria-label="{{ $label }}" @endif
     class="time-picker flex items-center gap-1.5 {{ $class }}">
    {{-- The submitted value, kept as one HH:MM string by the module. --}}
    <input type="hidden" id="{{ $id }}" @if ($name) name="{{ $name }}" @endif
           data-time-value value="{{ $stored }}"/>

    <div class="dropdown relative time-picker-hour">
        <select data-time-part="hour" data-dropdown class="sr-only" aria-label="Hour">
            {{-- "00" reads as an empty clock face; the value stays empty until
                 both parts are really chosen. --}}
            <option value="" disabled selected>00</option>

            @foreach ($hours as $option)
                <option value="{{ $option }}" @selected($hour === $option)>{{ sprintf('%02d', $option) }}</option>
            @endforeach
        </select>
    </div>

    <span class="heading shrink-0 text-xl leading-none text-slate-400 dark:text-slate-500" aria-hidden="true">:</span>

    <div class="dropdown relative time-picker-minute">
        <select data-time-part="minute" data-dropdown class="sr-only" aria-label="Minute">
            <option value="" disabled selected>00</option>

            @foreach (range(0, 59) as $option)
                <option value="{{ $option }}" @selected($minute === $option)>{{ sprintf('%02d', $option) }}</option>
            @endforeach
        </select>
    </div>

    @unless ($use24)
        <div class="dropdown relative time-picker-meridiem">
            <select data-time-part="meridiem" data-dropdown class="sr-only" aria-label="Morning or afternoon">
                <option value="am" @selected($meridiem === 'am')>AM</option>
                <option value="pm" @selected($meridiem === 'pm')>PM</option>
            </select>
        </div>
    @endunless
</div>
