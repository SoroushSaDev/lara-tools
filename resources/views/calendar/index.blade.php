@php
    use Carbon\Carbon;
@endphp
@extends('layouts.main')
@section('header-s')
    <a href="{{ route('home') }}" class="flex items-center space-x-2 text-xl hover:text-gray-300">
        <i class="bi bi-chevron-left"></i>
        <span class="invisible lg:visible">
            Home
        </span>
    </a>
@endsection
@section('title', 'Calendar')
@section('header-e')

@endsection
@section('content')
    <div class="w-full p-4">
        <div class="flex justify-between items-center max-sm:mb-5 sm:mb-20">
            <select id="month"
                    class="backdrop-blur-3xl bg-white/30 dark:bg-black/30 border-none p-2 rounded-lg hover:bg-white hover:text-black hover:shadow-2xl cursor-pointer">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}">
                        {{ Carbon::create()->month($m)->format('F') }}
                    </option>
                @endforeach
            </select>

            <a href="{{ route('calendar.index') }}" id="today"
               class="backdrop-blur-3xl border-none py-2 px-4 rounded-lg hover:bg-white hover:text-black hover:shadow-2xl {{ $date->isToday() ? 'bg-white text-black' : 'bg-white/30 dark:bg-black/30' }}">
                Today
            </a>

            <select id="year"
                    class="backdrop-blur-3xl bg-white/30 dark:bg-black/30 border-none p-2 pe-10 rounded-lg hover:bg-white hover:text-black hover:shadow-2xl cursor-pointer">
                @for($y = now()->year - 5; $y <= now()->year + 5; $y++)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>

        <div id="calendar" class="grid grid-cols-7 gap-2 text-center">
            <!-- Weekdays -->
            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="font-bold">{{ $day }}</div>
            @endforeach

            <!-- Days will be inserted here by JavaScript -->
        </div>
        <ul class="mt-5 backdrop-blur-3xl bg-white/30 dark:bg-black/30 p-3 rounded-lg text-sm space-y-3">
            @forelse($events as $event)
                <li>
                    {{ $event }}
                </li>
            @empty
                <li class="font-semibold">
                    No Events
                </li>
            @endforelse
        </ul>
    </div>

    <form action="{{ route('calendar.index') }}" method="GET">

    </form>
@endsection
@push('script')
    <script>
        function SetDate(element) {
            const yearEl = document.querySelector('#year');
            const monthEl = document.querySelector('#month');

            const year = yearEl.options[yearEl.selectedIndex].value;
            const month = monthEl.options[monthEl.selectedIndex].value;
            const day = element.textContent;

            // const date = new Date(year, month - 1, day);
            const date = `${year}-${month}-${day}`;

            window.location.replace('{{ route('calendar.index') }}?date=' + date);
        }

        function generateCalendar(month, year) {
            const calendar = document.getElementById('calendar');
            // Clear existing days (keep the weekdays)
            while (calendar.children.length > 7) {
                calendar.removeChild(calendar.lastChild);
            }

            const date = new Date(year, month - 1);
            const firstDay = new Date(date.getFullYear(), date.getMonth(), 1).getDay();
            const daysInMonth = new Date(year, month, 0).getDate();

            // Add blank days before the 1st day of month
            for (let i = 0; i < firstDay; i++) {
                const emptyCell = document.createElement('div');
                calendar.appendChild(emptyCell);
            }

            // Add actual days
            for (let day = 1; day <= daysInMonth; day++) {
                const dayCell = document.createElement('div');
                dayCell.textContent = day;

                const today = new Date();
                const isToday =
                    day === today.getDate() &&
                    month == (today.getMonth() + 1) &&
                    year == today.getFullYear();

                const isSelectedDay =
                    day == '{{ $date->day }}' &&
                    month == '{{ $date->month }}' &&
                    year == '{{ $date->year }}';

                dayCell.className = "backdrop-blur-3xl p-2 rounded-lg hover:bg-white hover:text-black hover:shadow-2xl cursor-pointer " + (isSelectedDay ? 'border-2 border-white ' : 'border-none ') + (isToday ? "bg-white text-black font-bold" : "bg-white/30 dark:bg-black/30");
                dayCell.onclick = () => SetDate(dayCell)
                calendar.appendChild(dayCell);
            }
        }

        window.addEventListener('beforeunload', function() {
            document.body.style.cursor = 'wait';
        });

        document.addEventListener('DOMContentLoaded', function () {
            const monthSelect = document.getElementById('month');
            const yearSelect = document.getElementById('year');

            const now = new Date();
            // monthSelect.value = now.getMonth() + 1;
            monthSelect.value = '{{ $date->month }}';
            yearSelect.value = '{{ $date->year }}';

            // yearSelect.value = now.getFullYear();

            function updateCalendar() {
                generateCalendar(monthSelect.value, yearSelect.value);
            }

            monthSelect.addEventListener('change', updateCalendar);
            yearSelect.addEventListener('change', updateCalendar);

            updateCalendar(); // initial render
        });
    </script>
@endpush

