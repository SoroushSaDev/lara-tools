{{-- Reusable filled-track slider. modules/slider.js owns --p and the tip;
     the native range input stays in the tree (opacity-0) for keyboard/a11y,
     so the `id` lands on the input and consumers keep using it directly. --}}
@props([
    'id' => null,
    'name' => null,
    'label' => null,
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'value' => 0,
    'format' => 'raw',
    'class' => '',
])

@php
    $min = (float) $min;
    $max = (float) $max;
    $percent = $max > $min ? min(100, max(0, ((float) $value - $min) / ($max - $min) * 100)) : 0;
@endphp

<div data-slider data-format="{{ $format }}" class="slider {{ $class }}" style="--p: {{ round($percent, 3) }}%">
    <div class="slider-rail">
        <div class="slider-fill"></div>
    </div>

    <div class="slider-thumb"></div>

    <input type="range" class="slider-input"
           id="{{ $id }}"
           @if ($name) name="{{ $name }}" @endif
           min="{{ $min }}" max="{{ $max }}" step="{{ $step }}" value="{{ $value }}"
           @if ($label) aria-label="{{ $label }}" @endif/>

    <output class="slider-tip" aria-hidden="true"></output>
</div>
