<?php

namespace App\Http\Controllers;

use App\Services\RecycleBinService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Recycle Bin: lists soft-deleted records and restores or purges them.
 */
class RecycleBinController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected RecycleBinService $bin,
    ) {}

    /**
     * Show every trashed record grouped by type.
     */
    public function index(): View
    {
        return view('bin.index', [
            'groups' => $this->bin->groupedTrash(),
        ]);
    }

    /**
     * Restore a trashed record.
     */
    public function restore(string $type, int $id): RedirectResponse
    {
        abort_unless($this->bin->handles($type), 404);

        return $this->bin->restore($type, $id)
            ? back()->with('success', 'Item restored.')
            : back()->with('error', 'That item no longer exists.');
    }

    /**
     * Permanently delete a single trashed record.
     */
    public function destroy(string $type, int $id): RedirectResponse
    {
        abort_unless($this->bin->handles($type), 404);

        return $this->bin->purge($type, $id)
            ? back()->with('success', 'Item permanently deleted.')
            : back()->with('error', 'That item no longer exists.');
    }

    /**
     * Permanently empty the entire recycle bin.
     */
    public function empty(): RedirectResponse
    {
        $removed = $this->bin->emptyAll();

        return redirect()
            ->route('bin.index')
            ->with($removed > 0 ? 'success' : 'info', $removed > 0
                ? "Recycle bin emptied ({$removed} ".str('item')->plural($removed).').'
                : 'The recycle bin is already empty.');
    }
}
