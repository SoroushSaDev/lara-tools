<div
    class="flex justify-between items-center max-sm:space-x-3 sm:space-x-6 backdrop-blur-3xl bg-white/30 dark:bg-black/30 p-3 rounded-lg hover:shadow-2xl hover:cursor-pointer">
    <div id="music-{{ $key }}" onclick="playMusic('{{ $key }}')"
         data-path="{{ asset('storage/' . $music->path)}}"
         data-title="{{ $music->title ?? $music->name }}"
         data-artist="{{ $music->artist }}"
         data-cover="{{ asset('storage/' . $music->cover_image) }}"
         class="flex items-center space-x-3 w-full">
        <img src="{{ asset('storage/' . $music->cover_image) }}" alt="Cover Art"
             class="max-sm:w-10 max-sm:h-10 sm:w-20 sm:h-20 object-cover rounded">
        <div class="relative overflow-hidden w-full">
            <h2 class="font-semibold sm:text-2xl">
                {{ $music->title ?? $music->name }}
            </h2>
            <div class="flex justify-between items-center max-sm:text-xs">
                <p class="whitespace-nowrap">
                    {{ $music->artist ?? 'Unknown artist' }} {{ $music->album ? (' | ' . $music->album) : '' }}
                </p>
                <span class="font-bold">
                    {{ formatTime($music->duration) }}
                </span>
            </div>
        </div>
    </div>
    <div>
        <button type="button"
                class="hover:cursor-pointer hover:backdrop-blur-3xl px-2 py-1 rounded-full sm:text-2xl"
                data-popover-target="menu-{{ $key }}" data-popover-placement="left" data-popover-trigger="click"
                data-dropdown-toggle="menu-{{ $key }}">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
        <form id="delete-{{ $music->id }}" action="{{ route('music.delete', $music) }}" method="post">
            @csrf
            @method('DELETE')
        </form>
    </div>
    {{--                @if ($music->duration)--}}
    {{--                    <p><strong>Duration:</strong> {{ gmdate('i\:s', $music->duration) }}</p>--}}
    {{--                @endif--}}
    {{--                <audio controls class="mt-2 w-full">--}}
    {{--                    <source src="{{ asset('storage/' . $music->path) }}">--}}
    {{--                    Your browser does not support the audio element.--}}
    {{--                </audio>--}}
</div>
<div id="menu-{{ $key }}"
     class="z-50 hidden my-4 max-sm:text-base sm:text-xl list-none backdrop-blur-3xl bg-white/30 dark:bg-black/30 divide-y divide-gray-100 rounded-lg shadow-2xl">
    <ul class="p-2 font-medium" role="none">
        <li>
            <a href="#" class="block px-4 py-2 hover:backdrop-blur-3xl rounded-lg" role="menuitem">
                <div class="inline-flex items-center space-x-3">
                    <i class="bi bi-file-earmark-music-fill"></i>
                    <span>
                    Add to playlist
                </span>
                </div>
            </a>
        </li>
        <li>
            <a href="#" class="block px-4 py-2 hover:backdrop-blur-3xl rounded-lg" role="menuitem">
                <div class="inline-flex items-center space-x-3">
                    <i class="bi bi-music-note-list"></i>
                    <span>
                    Add to queue
                </span>
                </div>
            </a>
        </li>
        <li>
            <button type="button"
                    class="flex justify-start px-4 py-2 text-red-500 hover:backdrop-blur-3xl rounded-lg cursor-pointer w-full"
                    role="menuitem" onclick="document.querySelector('#delete-{{ $music->id }}').submit();">
                <div class="inline-flex items-center space-x-3">
                    <i class="bi bi-trash3-fill"></i>
                    <span>
                  Remove
              </span>
                </div>
            </button>
        </li>
    </ul>
</div>
