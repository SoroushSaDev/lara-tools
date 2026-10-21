@extends('layouts.main')

@section('title', 'Calculator')

@section('content')
    <x-page-header back="{{ route('home') }}" back-label="Home">
        <x-slot:title>Calculator</x-slot:title>
    </x-page-header>

    @php
        // Shared keypad: rendered identically in the Standard and Scientific
        // panels so both drive the same [data-key] engine.
        $number = 'glass glass-hover !rounded-2xl h-14 cursor-pointer !px-0 font-display text-xl sm:h-16';
        $op = 'btn-ghost !rounded-2xl h-14 !px-0 text-lg sm:h-16';
        $keypad = [
            ['clear', 'C', 'btn-danger !rounded-2xl h-14 !px-0 sm:h-16 sm:text-lg'],
            ['backspace', '<i class="bi bi-backspace"></i>', $op],
            ['%', '%', $op],
            ['/', '&divide;', $op],
            ['7', '7', $number], ['8', '8', $number], ['9', '9', $number],
            ['*', '&times;', $op],
            ['4', '4', $number], ['5', '5', $number], ['6', '6', $number],
            ['-', '&minus;', $op],
            ['1', '1', $number], ['2', '2', $number], ['3', '3', $number],
            ['+', '+', $op],
            ['0', '0', $number.' col-span-2'],
            ['.', '.', $number],
            ['equals', '=', 'flex h-14 cursor-pointer items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-xl text-white shadow-lg shadow-violet-600/40 transition-all hover:brightness-110 active:scale-95 sm:h-16'],
        ];

        $science = [
            ['sin(', 'sin'], ['cos(', 'cos'], ['tan(', 'tan'],
            ['asin(', 'asin'], ['acos(', 'acos'], ['atan(', 'atan'],
            ['ln(', 'ln'], ['log10(', 'log'], ['exp(', 'e&#739;'],
            ['sqrt(', '&radic;'], ['cbrt(', '&root;'], ['π', '&pi;'],
            ['e', 'e'], ['^', 'x&#696;'], ['^2', 'x&sup2;'], ['^3', 'x&sup3;'],
            ['^(-1)', '1/x'], ['!', 'n!'], ['(', '('], [')', ')'], [' mod ', 'mod'],
        ];

        $stat = 'glass px-4 py-3';
        $statLabel = 'block text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500';
        $statValue = 'mt-1 block font-display text-2xl font-semibold tabular-nums';
    @endphp

    <div id="calculator" data-tabs class="w-full animate-fade-up lg:grid lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start lg:gap-6">
        {{-- Mode tabs: full-width strip, scrolls horizontally when narrow --}}
        <div class="glass mb-4 flex items-center gap-1.5 overflow-x-auto p-2 lg:col-span-2">
            <button type="button" data-tab="standard" class="tab-pill is-active shrink-0"><i class="bi bi-calculator"></i>Standard</button>
            <button type="button" data-tab="scientific" class="tab-pill shrink-0"><i class="bi bi-superscript"></i>Scientific</button>
            <button type="button" data-tab="bmi" class="tab-pill shrink-0"><i class="bi bi-person-bounding-box"></i>BMI</button>
            <button type="button" data-tab="date" class="tab-pill shrink-0"><i class="bi bi-calendar-range"></i>Date</button>
        </div>

        {{-- Mode panels share one measure so a keypad never swims in the column --}}
        <div class="mx-auto w-full min-w-0 max-w-2xl 2xl:max-w-3xl">
            {{-- Standard --}}
            <section data-tab-panel="standard">
                <div class="w-full">
                    <div class="glass flex min-h-28 flex-col items-end justify-center gap-1 p-5">
                        <input type="text" id="calc-expression" readonly
                               class="w-full bg-transparent text-right font-display text-3xl font-semibold tracking-wide text-slate-900 outline-none dark:text-white"
                               value="0"/>
                        <span id="calc-preview" class="min-h-6 w-full truncate text-right text-lg font-bold text-slate-400 dark:text-slate-500"></span>
                    </div>

                    <div class="mt-4 grid grid-cols-4 gap-2.5 sm:gap-3">
                        @foreach ($keypad as [$key, $label, $class])
                            <button type="button" data-key="{{ $key }}" class="{{ $class }}">{!! $label !!}</button>
                        @endforeach
                    </div>

                    <p class="mt-4 text-center text-xs font-bold text-slate-400">
                        Keyboard supported — digits, operators, Enter, Backspace, Esc.
                    </p>
                </div>
            </section>

            {{-- Scientific: extended key block above the same standard keypad --}}
            <section data-tab-panel="scientific" hidden>
                <div class="glass flex min-h-28 flex-col items-end justify-center gap-1 p-5">
                    <input type="text" id="sci-expression" readonly
                           class="w-full bg-transparent text-right font-display text-3xl font-semibold tracking-wide text-slate-900 outline-none dark:text-white"
                           value="0"/>
                    <span id="sci-preview" class="min-h-6 w-full truncate text-right text-lg font-bold text-slate-400 dark:text-slate-500"></span>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <p class="text-xs font-bold text-slate-400">
                        sin / cos / tan obey the angle unit; log = log&#8321;&#8320;, &radic; = sqrt.
                    </p>
                    <button type="button" id="calc-angle-toggle" data-action="angle-toggle"
                            class="btn-ghost !rounded-xl h-8 !px-3 text-xs" title="Toggle degrees / radians"
                            aria-label="Toggle degrees or radians">DEG</button>
                </div>

                <div class="mt-3 grid grid-cols-4 gap-1.5 sm:grid-cols-11">
                    @foreach ($science as [$key, $label])
                        <button type="button" data-key="{{ $key }}"
                                class="btn-ghost !rounded-xl h-11 !px-0 text-xs font-bold sm:h-12">{!! $label !!}</button>
                    @endforeach
                </div>

                <div class="mt-3 grid grid-cols-4 gap-2.5 sm:gap-3">
                    @foreach ($keypad as [$key, $label, $class])
                        <button type="button" data-key="{{ $key }}" class="{{ $class }}">{!! $label !!}</button>
                    @endforeach
                </div>
            </section>

            {{-- BMI --}}
            <section data-tab-panel="bmi" id="bmi-panel" hidden>
                <div class="glass p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="heading flex items-center gap-2 text-lg">
                            <i class="bi bi-person-bounding-box text-violet-500 dark:text-fuchsia-400"></i>Body Mass Index
                        </h2>

                        <div class="flex items-center gap-1 rounded-full border border-slate-900/10 p-1 dark:border-white/10">
                            <button type="button" data-bmi-unit="metric" class="tab-pill is-active !px-3 !py-1.5 text-xs">kg / cm</button>
                            <button type="button" data-bmi-unit="imperial" class="tab-pill !px-3 !py-1.5 text-xs">lb / in</button>
                        </div>
                    </div>

                    <div class="mt-5" data-bmi-fields="metric">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label>
                                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">Weight (kg)</span>
                                <input type="number" min="0" step="0.1" inputmode="decimal" placeholder="70"
                                       data-bmi-role="weight" id="bmi-weight-metric" class="field-input"/>
                            </label>
                            <label>
                                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">Height (cm)</span>
                                <input type="number" min="0" step="0.1" inputmode="decimal" placeholder="175"
                                       data-bmi-role="height" id="bmi-height-metric" class="field-input"/>
                            </label>
                        </div>
                    </div>

                    <div class="mt-5" data-bmi-fields="imperial" hidden>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label>
                                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">Weight (lb)</span>
                                <input type="number" min="0" step="0.1" inputmode="decimal" placeholder="154"
                                       data-bmi-role="weight" id="bmi-weight-imperial" class="field-input"/>
                            </label>
                            <label>
                                <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">Height (in)</span>
                                <input type="number" min="0" step="0.1" inputmode="decimal" placeholder="69"
                                       data-bmi-role="height" id="bmi-height-imperial" class="field-input"/>
                            </label>
                        </div>
                    </div>

                    <p id="bmi-error" class="field-error"></p>

                    {{-- No display utility on this element: it is toggled via the hidden attribute --}}
                    <div id="bmi-result" hidden>
                        <div class="glass mt-4 flex flex-wrap items-center gap-x-8 gap-y-4 p-5">
                            <div>
                                <span class="{{ $statLabel }}">BMI</span>
                                <span id="bmi-value" class="font-display text-4xl font-semibold tabular-nums">&ndash;</span>
                            </div>

                            <span id="bmi-category" class="chip">&ndash;</span>

                            <div>
                                <span class="{{ $statLabel }}">Healthy weight for this height</span>
                                <span id="bmi-range" class="mt-1 block font-display text-lg font-semibold tabular-nums">&ndash;</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Date difference --}}
            <section data-tab-panel="date" id="date-panel" hidden>
                <div class="glass p-5 sm:p-6">
                    <h2 class="heading flex items-center gap-2 text-lg">
                        <i class="bi bi-calendar-range text-violet-500 dark:text-fuchsia-400"></i>Date Difference
                    </h2>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <label>
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">First date</span>
                            <input type="date" id="date-from" class="field-input"/>
                        </label>
                        <label>
                            <span class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500">Second date</span>
                            <input type="date" id="date-to" class="field-input"/>
                        </label>
                    </div>

                    <p id="date-error" class="field-error"></p>

                    <div id="date-result" hidden>
                        <p id="date-note" class="mt-4 text-sm font-bold text-slate-500 dark:text-slate-400"></p>

                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div class="{{ $stat }}">
                                <span class="{{ $statLabel }}">Years / Months / Days</span>
                                <p id="date-ymd" class="{{ $statValue }}">&ndash;</p>
                            </div>
                            <div class="{{ $stat }}">
                                <span class="{{ $statLabel }}">Total weeks + days</span>
                                <p id="date-weeks" class="{{ $statValue }}">&ndash;</p>
                            </div>
                            <div class="{{ $stat }}">
                                <span class="{{ $statLabel }}">Total days</span>
                                <p id="date-days" class="{{ $statValue }}">&ndash;</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- History — shared by Standard and Scientific, hidden for BMI/Date --}}
        <aside id="calc-history-aside" class="glass mt-4 p-4 animate-fade-up lg:sticky lg:top-6 lg:mt-0">
            <div class="mb-3 flex items-center justify-between gap-2">
                <h2 class="heading flex items-center gap-2 text-base">
                    <i class="bi bi-clock-history text-violet-500 dark:text-fuchsia-400"></i>History
                </h2>

                <button type="button" id="calc-history-clear" class="icon-btn size-8 !border-0" title="Clear history">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>

            <ul id="calc-history" class="flex max-h-[28rem] flex-col gap-2 overflow-y-auto"></ul>

            <p id="calc-history-empty" class="py-6 text-center text-sm font-bold text-slate-400 dark:text-slate-500">
                Nothing calculated yet.
            </p>
        </aside>
    </div>
@endsection
