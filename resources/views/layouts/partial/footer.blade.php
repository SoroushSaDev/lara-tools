<div class="fixed bottom-0 left-0 w-full p-3 lg:hidden transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
    <div class="grid grid-cols-3 gap-3 backdrop-blur-3xl bg-white.30 dark:bg-black/30 rounded-md shadow-2xl p-2">
        <a href="{{ route('bin') }}" class="flex flex-col items-center hover:text-gray-300 text-md">
            <i class="bi bi-trash3{{ request()->is('bin*') ? '-fill' : '' }} text-lg"></i>
            <span class="text-xs font-semibold">
                Recycle Bin
            </span>
        </a>
        <a href="{{ route('home') }}" class="flex flex-col items-center hover:text-gray-300 text-md">
            <i class="bi bi-house{{ request()->is('/') ? '-fill' : '' }} text-lg"></i>
            <span class="text-xs font-semibold">
                Home
            </span>
        </a>
        <a href="{{ route('settings') }}" class="flex flex-col items-center hover:text-gray-300 text-md">
            <i class="bi bi-gear{{ request()->is('settings*') ? '-fill' : '' }} text-lg"></i>
            <span class="text-xs font-semibold">
                Settings
            </span>
        </a>
    </div>
</div>
