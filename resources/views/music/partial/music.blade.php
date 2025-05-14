<div
    class="flex justify-between items-center backdrop-blur-3xl bg-white/30 dark:bg-black/30 p-3 rounded-lg hover:shadow-2xl hover:cursor-pointer">
    <div id="music-{{ $key }}" onclick="playMusic('{{ $key }}')"
        data-path="{{ asset('storage/' . $music->path)}}"
        data-title="{{ $music->title ?? $music->name }}"
        data-artist="{{ $music->artist }}"
        data-cover="{{ asset('storage/' . $music->cover_image) }}"
        class="flex items-center space-x-3">
        <img src="{{ asset('storage/' . $music->cover_image) }}" alt="Cover Art"
             class="w-10 h-10 object-cover rounded">
        <div class="relative overflow-hidden">
            <h2 class="font-semibold">
                {{ $music->title ?? $music->name }}
            </h2>
            <p class="text-sm whitespace-nowrap">
                {{ $music->artist ?? 'Unknown artist' }} {{ $music->album ? (' | ' . $music->album) : '' }}
            </p>
        </div>
    </div>
    <div>
        <button type="button"
                class="hover:cursor-pointer hover:backdrop-blur-3xl px-2 py-1 rounded-full"
                data-popover-target="menu-{{ $key }}" data-popover-placement="left" data-popover-trigger="click" data-dropdown-toggle="menu-{{ $key }}">
            <i class="bi bi-three-dots-vertical"></i>
        </button>
    </div>
    {{--                @if ($music->duration)--}}
    {{--                    <p><strong>Duration:</strong> {{ gmdate('i\:s', $music->duration) }}</p>--}}
    {{--                @endif--}}
    {{--                <audio controls class="mt-2 w-full">--}}
    {{--                    <source src="{{ asset('storage/' . $music->path) }}">--}}
    {{--                    Your browser does not support the audio element.--}}
    {{--                </audio>--}}
</div>
<div id="menu-{{ $key }}" class="z-50 hidden my-4 text-base list-none backdrop-blur-3xl bg-white/30 dark:bg-black/30 divide-y divide-gray-100 rounded-lg shadow-2xl">
    <ul class="py-2 font-medium" role="none">
        <li>
            <a href="#" class="block px-4 py-2 text-sm hover:backdrop-blur-3xl role="menuitem">
              <div class="inline-flex items-center space-x-3">
                <i class="bi bi-music-note-list"></i>
                <span>
                    Add to playlist
                </span>
              </div>
            </a>
        </li>
        <li>
          <a href="#" class="block px-4 py-2 text-sm text-red-500 hover:backdrop-blur-3xl" role="menuitem">
            <div class="inline-flex items-center space-x-3">
              <i class="bi bi-trash3-fill"></i>
              <span>
                  Remove
              </span>
            </div>
          </a>
        </li>
    </ul>
</div>
