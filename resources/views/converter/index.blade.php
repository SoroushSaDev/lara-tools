@extends('layouts.main')

@section('title', 'Unit Converter')

@section('content')
    <x-page-header back="{{ route('home') }}" back-label="Home">
        <x-slot:title>Unit Converter</x-slot:title>

        <x-slot:actions>
            <a href="{{ route('settings', ['tab' => 'converter']) }}" class="icon-btn" title="Converter settings">
                <i class="bi bi-gear"></i>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div id="converter-root" data-precision="{{ $converterPrecision }}"
         class="lg:grid lg:grid-cols-[220px_minmax(0,1fr)] lg:items-start lg:gap-6">
        {{-- Categories: horizontal strip below lg, vertical rail above --}}
        <div id="conv-cats" class="glass mb-5 flex h-fit flex-wrap items-center gap-2 p-2 animate-fade-up lg:sticky lg:top-6 lg:mb-0 lg:max-h-[calc(100vh-3rem)] lg:flex-col lg:items-stretch lg:gap-1.5 lg:overflow-y-auto lg:p-2.5"></div>

        {{-- Main converter card — @container makes the From/To split depend on
             this card's own width, which the category rail squeezes on smaller
             viewports. --}}
        <div class="glass mx-auto max-w-3xl @container animate-fade-up lg:w-full xl:max-w-none">
            <div class="relative p-6 sm:p-8">
                {{-- From / To rows --}}
                <div class="grid gap-4 @[52rem]:grid-cols-2 @[52rem]:gap-16">
                    {{-- From --}}
                    <div class="relative">
                        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-violet-500/5 to-fuchsia-500/5 rounded-2xl" aria-hidden="true"></div>

                        <div class="relative flex flex-col gap-4 p-4 sm:p-5">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                From
                            </p>

                            <div class="flex items-center gap-3">
                                <input type="number" id="conv-input" value="1" step="any" autocomplete="off"
                                       class="field-input flex-1 min-w-0 text-right font-display text-xl tabular-nums"
                                       aria-label="Input value"/>
                                <div class="dropdown relative flex-1 min-w-0">
                                    <select id="conv-from" data-dropdown class="sr-only" aria-label="From unit"></select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- To --}}
                    <div class="relative">
                        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 rounded-2xl" aria-hidden="true"></div>

                        <div class="relative flex flex-col gap-4 p-4 sm:p-5">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                To
                            </p>

                            <div class="flex items-center gap-3">
                                <output id="conv-output"
                                        class="field-input flex-1 min-w-0 flex items-center justify-end overflow-x-auto text-right font-display text-xl font-bold tabular-nums text-emerald-600 dark:text-teal-400"
                                        aria-label="Converted value">—</output>
                                <div class="dropdown relative flex-1 min-w-0">
                                    <select id="conv-to" data-dropdown class="sr-only" aria-label="To unit"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Swap button centered in the gutter between the two panels --}}
                <div class="mt-4 flex justify-center @[52rem]:absolute @[52rem]:inset-y-0 @[52rem]:left-1/2 @[52rem]:mx-0 @[52rem]:flex @[52rem]:items-center @[52rem]:-translate-x-1/2">
                    <button type="button" id="conv-swap"
                            class="icon-btn size-11 rotate-0 transition-transform duration-300 hover:rotate-180 hover:scale-110
                                   bg-gradient-to-br from-violet-500 to-fuchsia-500 text-white shadow-lg shadow-violet-500/30
                                   hover:shadow-xl hover:shadow-fuchsia-500/40"
                            title="Swap units"
                            aria-label="Swap units">
                        <i class="bi bi-arrow-left-right"></i>
                    </button>
                </div>
            </div>

            {{-- Quick precision hint --}}
            <div class="border-t border-slate-900/10 dark:border-white/10 px-6 py-4 sm:px-8">
                <p class="text-center text-xs font-bold text-slate-400 dark:text-slate-500">
                    Precision: {{ $converterPrecision }} decimal{{ $converterPrecision !== 1 ? 's' : '' }} —
                    <a href="{{ route('settings', ['tab' => 'converter']) }}" class="text-violet-500 hover:underline">
                        adjust in settings
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection