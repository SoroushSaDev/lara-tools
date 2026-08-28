{{-- Mobile bottom navigation --}}
<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-white/50 bg-white/70 backdrop-blur-2xl lg:hidden dark:border-white/10 dark:bg-black/50">
    <div class="mx-auto grid max-w-lg grid-cols-5 px-2 pt-1.5 pb-[max(0.5rem,env(safe-area-inset-bottom))]">
        <x-nav-link name="home" icon="bi-grid-1x2-fill" label="Home" exact compact/>
        <x-nav-link name="notes.index" icon="bi-journal-text" label="Notes" compact/>
        <x-nav-link name="todos.index" icon="bi-list-check" label="Todos" compact/>
        <x-nav-link name="music.index" icon="bi-music-note-beamed" label="Music" compact/>
        <x-nav-link name="weather.index" icon="bi-cloud-sun-fill" label="Weather" compact/>
    </div>
</nav>
