<?php

namespace App\Http\Controllers;

use App\Contracts\SettingRepository;
use App\Support\Timezones;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Clock tool: real-time clock, stopwatch, timers and world clocks.
 */
class ClockController extends Controller
{
    /**
     * Setting key holding the saved world-clock timezones (JSON list).
     *
     * @var string
     */
    protected const ZONES_KEY = 'world-clocks';

    /**
     * Maximum number of world clocks a user can pin.
     *
     * @var int
     */
    protected const MAX_ZONES = 8;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SettingRepository $settings,
    ) {}

    /**
     * Show the clock tool.
     */
    public function index(): View
    {
        $zones = $this->savedZones();

        return view('clock.index', [
            'zones' => $zones,
            'zoneLabels' => Timezones::names($zones),
            'available' => Timezones::grouped($zones),
        ]);
    }

    /**
     * Add or remove one world-clock zone.
     */
    public function saveZones(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'add' => ['sometimes', 'required', 'string', Rule::in(Timezones::identifiers())],
            'remove' => ['sometimes', 'required', 'string', Rule::in(Timezones::identifiers())],
        ]);

        $zones = $this->savedZones();

        if (isset($validated['add'])) {
            if (in_array($validated['add'], $zones)) {
                return back()->with('info', 'That clock is already pinned.');
            }

            if (count($zones) >= self::MAX_ZONES) {
                return back()->with('error', 'At most '.self::MAX_ZONES.' world clocks can be pinned.');
            }

            $zones[] = $validated['add'];
        }

        if (isset($validated['remove'])) {
            $zones = array_values(array_diff($zones, [$validated['remove']]));
        }

        $this->settings->set(self::ZONES_KEY, json_encode(array_values($zones)));

        return back()->with('success', 'World clocks updated.');
    }

    /**
     * Saved timezone identifiers in pinning order.
     *
     * @return list<string>
     */
    protected function savedZones(): array
    {
        $decoded = json_decode((string) $this->settings->get(self::ZONES_KEY, '[]'), true);

        $valid = Timezones::identifiers();

        // Filter against known zones so a typo in the stored list never renders.
        return array_values(array_intersect(is_array($decoded) ? $decoded : [], $valid));
    }
}
