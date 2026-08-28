@extends('layouts.main')

@section('title', 'Calculator')

@section('content')
    <x-page-header back="{{ route('home') }}" back-label="Home">
        <x-slot:title>Calculator</x-slot:title>
    </x-page-header>

    <div id="calculator" class="mx-auto w-full max-w-md animate-fade-up">
        {{-- Display --}}
        <div class="glass flex min-h-28 flex-col items-end justify-center gap-1 p-5">
            <input type="text" id="calc-expression" readonly
                   class="w-full bg-transparent text-right font-display text-3xl font-semibold tracking-wide text-slate-900 outline-none dark:text-white"
                   value="0"/>
            <span id="calc-preview" class="min-h-6 w-full truncate text-right text-lg font-bold text-slate-400 dark:text-slate-500"></span>
        </div>

        {{-- Keypad --}}
        <div class="mt-4 grid grid-cols-4 gap-2.5 sm:gap-3">
            <button type="button" data-key="clear"
                    class="btn-danger !rounded-2xl h-14 !px-0 sm:h-16 sm:text-lg">C</button>

            <button type="button" data-key="backspace"
                    class="btn-ghost !rounded-2xl h-14 !px-0 sm:h-16 sm:text-lg"><i class="bi bi-backspace"></i></button>

            <button type="button" data-key="%"
                    class="btn-ghost !rounded-2xl h-14 !px-0 text-lg sm:h-16">%</button>

            <button type="button" data-key="/"
                    class="btn-ghost !rounded-2xl h-14 !px-0 text-lg sm:h-16">&divide;</button>

            @foreach ([['7', '7'], ['8', '8'], ['9', '9']] as [$key, $label])
                <button type="button" data-key="{{ $key }}"
                        class="glass glass-hover !rounded-2xl h-14 cursor-pointer !px-0 font-display text-xl sm:h-16">{{ $label }}</button>
            @endforeach

            <button type="button" data-key="*"
                    class="btn-ghost !rounded-2xl h-14 !px-0 text-lg sm:h-16">&times;</button>

            @foreach (['4', '5', '6'] as $key)
                <button type="button" data-key="{{ $key }}"
                        class="glass glass-hover !rounded-2xl h-14 cursor-pointer !px-0 font-display text-xl sm:h-16">{{ $key }}</button>
            @endforeach

            <button type="button" data-key="-"
                    class="btn-ghost !rounded-2xl h-14 !px-0 text-lg sm:h-16">&minus;</button>

            @foreach (['1', '2', '3'] as $key)
                <button type="button" data-key="{{ $key }}"
                        class="glass glass-hover !rounded-2xl h-14 cursor-pointer !px-0 font-display text-xl sm:h-16">{{ $key }}</button>
            @endforeach

            <button type="button" data-key="+"
                    class="btn-ghost !rounded-2xl h-14 !px-0 text-lg sm:h-16">+</button>

            <button type="button" data-key="0"
                    class="glass glass-hover col-span-2 !rounded-2xl h-14 cursor-pointer !px-0 font-display text-xl sm:h-16">0</button>

            <button type="button" data-key="."
                    class="glass glass-hover !rounded-2xl h-14 cursor-pointer !px-0 font-display text-xl sm:h-16">.</button>

            <button type="button" data-key="equals"
                    class="flex h-14 cursor-pointer items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-xl text-white shadow-lg shadow-violet-600/40 transition-all hover:brightness-110 active:scale-95 sm:h-16">
                =
            </button>
        </div>

        <p class="mt-4 hidden text-center text-xs font-bold text-slate-400 sm:block">
            Keyboard supported — digits, operators, Enter, Backspace, Esc.
        </p>
    </div>
@endsection
