@extends('layouts.main')
@section('title', 'Home')
@section('description', 'Pick your tool')
@section('content')
    <div class="grid grid-cols-2 gap-3 lg:gap-6 mt-5 lg:mt-0">
        <a href="{{ route('notes.index') }}" class="flex flex-col justify-center items-center space-y-5 lg:space-y-10 font-semibold text-lg lg:text-2xl p-10 lg:p-20 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl">
            <i class="bi bi-sticky-fill text-5xl lg:text-6xl"></i>
            <span>
                Notes
            </span>
        </a>
        <a href="{{ route('notes.index') }}" class="flex flex-col justify-center items-center space-y-5 lg:space-y-10 font-semibold text-lg lg:text-2xl p-10 lg:p-20 backdrop-blur-3xl bg-white/30 dark:bg-black/30 rounded-lg hover:shadow-2xl">
            <i class="bi bi-calculator-fill text-5xl lg:text-6xl"></i>
            <span>
                Calculator
            </span>
        </a>
    </div>
@endsection
