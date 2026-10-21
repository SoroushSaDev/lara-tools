<?php

namespace App\Http\Controllers;

use App\Models\Music;
use App\Models\Note;
use App\Models\Todo;
use App\Support\RichText;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * One term across notes, todo lists and the music library.
 */
class SearchController extends Controller
{
    /**
     * Rows listed per module before the "see all" link takes over.
     *
     * @var int
     */
    protected const PER_MODULE = 6;

    /**
     * Frame the search overlay reloads on every keystroke.
     *
     * @var string
     */
    protected const FRAME = 'global-search-results';

    /**
     * Show matches for the term, or the resting hint when nothing is typed.
     */
    public function index(Request $request): View
    {
        $term = Search::term($request->query('q'));
        $modules = $this->modules($term);

        $data = [
            'term' => $term,
            'query' => $request->query('q'),
            'modules' => $modules,
            'count' => array_sum(array_column($modules, 'total')),
        ];

        // The overlay only wants the frame; visiting the route directly still
        // lands on a real page instead of a bare fragment.
        return $request->header('Turbo-Frame') === self::FRAME
            ? view('search.partial.results', $data)
            : view('search.index', $data);
    }

    /**
     * Non-empty match groups, each with the rows to link straight to.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function modules(?string $term): array
    {
        $groups = [
            $this->group('notes', 'Notes', 'notes.index', 'bi-journal-text',
                Search::filter(Note::latest()->get(), $term),
                $this->describeNote(...)),

            $this->group('todos', 'Todo lists', 'todos.index', 'bi-list-check',
                Search::filter(Todo::latest()->get(), $term),
                $this->describeTodo(...)),

            $this->group('music', 'Music', 'music.index', 'bi-music-note-beamed',
                Search::filter(Music::latest()->get(), $term),
                $this->describeTrack(...)),
        ];

        return array_values(array_filter($groups, fn (array $group): bool => $group['total'] > 0));
    }

    /**
     * Build one module's group, describing only the rows worth showing.
     *
     * @param  Collection<int, object>  $rows
     * @param  callable(object): array{href: string, title: string, meta: string|null}  $describe
     * @return array<string, mixed>
     */
    protected function group(string $key, string $label, string $route, string $icon, Collection $rows, callable $describe): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'route' => $route,
            'total' => $rows->count(),
            'hits' => $rows->take(self::PER_MODULE)
                ->map(fn (object $row): array => [...$describe($row), 'icon' => $icon])
                ->all(),
        ];
    }

    /**
     * @return array{href: string, title: string, meta: string|null}
     */
    protected function describeNote(Note $note): array
    {
        return [
            'href' => route('notes.show', $note),
            'title' => $note->title ?: 'Untitled',
            'meta' => Str::limit(RichText::toPlainText((string) $note->body), 90) ?: null,
        ];
    }

    /**
     * @return array{href: string, title: string, meta: string|null}
     */
    protected function describeTodo(Todo $todo): array
    {
        $progress = $todo->progress();

        return [
            'href' => route('todos.show', $todo),
            'title' => $todo->title,
            'meta' => $progress['completed'].'/'.$progress['total'].' done',
        ];
    }

    /**
     * A track has no page of its own, so its result lands on the library
     * filtered to that title — one click away from playing it.
     *
     * @return array{href: string, title: string, meta: string|null}
     */
    protected function describeTrack(Music $track): array
    {
        return [
            'href' => route('music.index', ['q' => $track->displayTitle()]),
            'title' => $track->displayTitle(),
            'meta' => collect([$track->artist, $track->album])->filter()->implode(' · ') ?: null,
        ];
    }
}
