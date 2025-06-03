@extends('layouts.main')
@section('header-s')
    <a href="{{ route('home') }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-chevron-left"></i>
        <span class="invisible lg:visible">
            Home
        </span>
    </a>
@endsection
@section('title', 'Calculator')
@section('header-e')

@endsection
@section('content')
    <div class="max-sm:w-full h-100">
        <input type="text" id="calc-display"
               class="text-3xl font-semibold backdrop-blur-3xl bg-white/30 dark:bg-black/30 border-none rounded-md w-full p-3"
               disabled>
        <div class="grid grid-cols-4 gap-2 mt-10">
            <button onclick="Clear();"
                    class="backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                C
            </button>
            <button onclick="Backspace();"
                    class="backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                ⌫
            </button>
            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                %
            </button>
            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                /
            </button>

            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                7
            </button>
            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                8
            </button>
            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                9
            </button>
            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                x
            </button>

            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                4
            </button>
            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                5
            </button>
            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                6
            </button>
            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                -
            </button>

            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                1
            </button>
            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                2
            </button>
            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                3
            </button>
            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                +
            </button>

            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                0
            </button>
            <button
                class="calc-btn backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                .
            </button>
            <button onclick="Calculate();"
                    class="col-span-2 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl hover:cursor-pointer h-20 text-3xl">
                =
            </button>
        </div>
    </div>
@endsection
@push('script')
    <script>
        let current = '';
        const buttons = document.querySelectorAll('.calc-btn');
        const display = document.getElementById('calc-display');

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                let value = btn.textContent.trim();

                current += value;
                display.value = current;
            });
        });

        function Backspace() {
            current = current.slice(0, -1);
            display.value = current;
        }

        function Calculate() {
            current = current.replaceAll('x', '*');

            try {
                current = eval(current).toString();
            } catch {
                current = 'Error';
            }

            display.value = current;
        }

        function Clear() {
            current = '';
            display.value = current;
        }
    </script>
@endpush
