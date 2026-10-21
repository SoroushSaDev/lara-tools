{{-- Settings toggle: hidden "0" plus checkbox "1", so an untouched
     switch still reaches the server and partial saves never reset it. --}}
@props(['name', 'checked' => false])

<span class="relative inline-flex shrink-0">
    <input type="hidden" name="{{ $name }}" value="0"/>
    <input type="checkbox" name="{{ $name }}" value="1"
           class="peer sr-only" @checked($checked)/>

    <span class="block h-7 w-12 rounded-full bg-slate-300 transition-colors peer-checked:bg-gradient-to-r peer-checked:from-violet-600 peer-checked:to-fuchsia-600 dark:bg-slate-600"></span>
    <span class="pointer-events-none absolute left-1 top-1 size-5 rounded-full bg-white shadow-md transition-transform peer-checked:translate-x-5"></span>
</span>
