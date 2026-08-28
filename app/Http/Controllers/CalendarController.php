<?php

namespace App\Http\Controllers;

use App\Contracts\HolidayProvider;
use App\Contracts\SettingRepository;
use App\Http\Requests\EventRequest;
use App\Models\Event;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Month calendar combining user events with public holidays.
 */
class CalendarController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected HolidayProvider $holidays,
        protected SettingRepository $settings,
    ) {}

    /**
     * Display the calendar for the requested (or current) date.
     */
    public function index(Request $request): View
    {
        $date = $this->resolveDate($request->query('date'));
        $monthStart = $date->copy()->startOfMonth();
        $monthEnd = $date->copy()->endOfMonth();

        // Events for the whole month so every day can show its dot indicator.
        $eventsByDay = Event::query()
            ->whereBetween('starts_at', [$monthStart, $monthEnd])
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Event $event) => (int) $event->starts_at->format('j'));

        // Holidays are provided on the Jalali calendar, so convert first.
        $jalali = \Morilog\Jalali\Jalalian::fromDateTime($date);
        $holidays = $this->holidays->forDate($jalali->getYear(), $jalali->getMonth(), $jalali->getDay());

        return view('calendar.index', [
            'date' => $date,
            'events' => $eventsByDay->get((int) $date->format('j'), collect()),
            'eventDays' => $eventsByDay->keys()->all(),
            'holidays' => $holidays,
            'weekStart' => (int) ($this->settings->get('calendar_week_start') ?? 0),
            'holidaysEnabled' => ($this->settings->get('calendar_show_holidays') ?? '1') === '1',
        ]);
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(Request $request): View
    {
        return view('calendar.create', [
            'date' => $this->resolveDate($request->query('date')),
        ]);
    }

    /**
     * Store a newly created event.
     */
    public function store(EventRequest $request): RedirectResponse
    {
        Event::create($request->validatedPayload());

        return redirect()
            ->route('calendar.index', ['date' => $request->validatedPayload()['starts_at']])
            ->with('success', 'Event created.');
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event): View
    {
        return view('calendar.edit', [
            'event' => $event,
            'date' => $event->starts_at->copy(),
        ]);
    }

    /**
     * Update the specified event.
     */
    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        $event->update($request->validatedPayload());

        return redirect()
            ->route('calendar.index', ['date' => $event->starts_at->toDateTimeString()])
            ->with('success', 'Event updated.');
    }

    /**
     * Soft-delete the specified event.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $day = $event->starts_at->toDateString();

        $event->delete();

        return redirect()
            ->route('calendar.index', ['date' => $day])
            ->with('success', 'Event moved to the recycle bin.');
    }

    /**
     * Parse a query-string date, falling back to today when missing or invalid.
     */
    protected function resolveDate(?string $value): CarbonInterface
    {
        if (filled($value)) {
            try {
                return Carbon::parse($value);
            } catch (InvalidFormatException) {
                // Fall through to today for malformed input.
            }
        }

        return Carbon::today();
    }
}
