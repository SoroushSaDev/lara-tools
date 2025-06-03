<div class="flex justify-between items-center w-full">
    <span>
        {{ $label }}
    </span>
    <span class="font-semibold">
        {{ $value . ($unit ?? '') }}
    </span>
</div>
