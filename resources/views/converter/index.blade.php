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

    <div id="converter-root" data-precision="{{ $converterPrecision }}">
        {{-- Category pills (rendered from the JS unit registry) --}}
        <div id="conv-cats" class="glass mb-5 flex flex-wrap items-center gap-2 p-2 animate-fade-up"></div>

        <div class="glass mx-auto max-w-xl p-6 animate-fade-up sm:p-8">
            {{-- From --}}
            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                From
            </label>

            <div class="flex gap-3">
                <input type="number" id="conv-input" value="1" step="any" autocomplete="off"
                       class="field-input !w-32 text-right font-display !text-lg tabular-nums"/>
                <select id="conv-from" class="field-input flex-1 cursor-pointer appearance-none"></select>
            </div>

            {{-- Swap --}}
            <div class="my-4 flex justify-center">
                <button type="button" id="conv-swap"
                        class="icon-btn size-11 rotate-0 transition-transform duration-300 hover:rotate-180"
                        title="Swap units">
                    <i class="bi bi-arrow-down-up"></i>
                </button>
            </div>

            {{-- To --}}
            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                To
            </label>

            <div class="flex gap-3">
                <output id="conv-output"
                        class="field-input flex !w-40 items-center justify-end overflow-x-auto text-right font-display !text-lg font-bold tabular-nums text-violet-600 dark:text-fuchsia-300">—</output>
                <select id="conv-to" class="field-input flex-1 cursor-pointer appearance-none"></select>
            </div>
        </div>
    </div>
@endsection
