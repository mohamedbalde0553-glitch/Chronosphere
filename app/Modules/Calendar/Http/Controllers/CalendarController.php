<?php

namespace App\Modules\Calendar\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Calendar\Models\Calendar;
use App\Modules\Calendar\Models\Event;
use App\Modules\Calendar\Models\EventCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        if ($user->calendars()->count() === 0) {
            Calendar::create([
                'user_id'    => $user->id,
                'name'       => 'Personnel',
                'color'      => '#7C3AED',
                'is_default' => true,
            ]);
        }

        $calendars  = $user->calendars()->orderByDesc('is_default')->orderBy('name')->get();
        $categories = EventCategory::where('user_id', $user->id)
            ->orWhereNull('user_id')
            ->orderBy('name')
            ->get();

        return view('modules.calendar.index', compact('calendars', 'categories'));
    }

    public function feed(Request $request): JsonResponse
    {
        $user  = auth()->user();
        $start = $request->query('start', now()->startOfMonth()->toDateTimeString());
        $end   = $request->query('end', now()->endOfMonth()->toDateTimeString());

        $calendarIds = $user->calendars()->pluck('id');

        $events = Event::whereIn('calendar_id', $calendarIds)
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->with(['calendar', 'category'])
            ->get();

        return response()->json($events->map(fn (Event $e) => [
            'id'              => $e->id,
            'title'           => $e->title,
            'start'           => $e->start_at->toIso8601String(),
            'end'             => $e->end_at->toIso8601String(),
            'allDay'          => (bool) $e->is_all_day,
            'backgroundColor' => $e->color ?? $e->calendar->color,
            'borderColor'     => $e->color ?? $e->calendar->color,
            'extendedProps'   => [
                'description'   => $e->description,
                'location'      => $e->location,
                'status'        => $e->status,
                'calendar_id'   => $e->calendar_id,
                'calendar_name' => $e->calendar->name,
                'calendar_color'=> $e->calendar->color,
                'category_id'   => $e->category_id,
            ],
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:191',
            'calendar_id' => 'required|exists:cal_calendars,id',
            'start_at'    => 'required|date',
            'end_at'      => 'required|date|after_or_equal:start_at',
            'is_all_day'  => 'boolean',
            'description' => 'nullable|string',
            'location'    => 'nullable|string|max:191',
            'color'       => 'nullable|string|max:7',
            'category_id' => 'nullable|exists:cal_event_categories,id',
            'status'      => 'nullable|string|in:confirmed,tentative,cancelled',
        ]);

        $this->authorizeCalendar($data['calendar_id']);

        $event = Event::create($data + ['user_id' => auth()->id()]);
        $event->load('calendar');

        return response()->json([
            'id'              => $event->id,
            'title'           => $event->title,
            'start'           => $event->start_at->toIso8601String(),
            'end'             => $event->end_at->toIso8601String(),
            'allDay'          => (bool) $event->is_all_day,
            'backgroundColor' => $event->color ?? $event->calendar->color,
            'borderColor'     => $event->color ?? $event->calendar->color,
            'extendedProps'   => [
                'description'   => $event->description,
                'location'      => $event->location,
                'calendar_id'   => $event->calendar_id,
                'calendar_name' => $event->calendar->name,
            ],
        ], 201);
    }

    public function show(Event $event): JsonResponse
    {
        $this->authorizeEvent($event);

        return response()->json($event->load(['calendar', 'category']));
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $this->authorizeEvent($event);

        $data = $request->validate([
            'title'       => 'sometimes|required|string|max:191',
            'calendar_id' => 'sometimes|exists:cal_calendars,id',
            'start_at'    => 'sometimes|required|date',
            'end_at'      => 'sometimes|required|date',
            'is_all_day'  => 'boolean',
            'description' => 'nullable|string',
            'location'    => 'nullable|string|max:191',
            'color'       => 'nullable|string|max:7',
            'category_id' => 'nullable|exists:cal_event_categories,id',
            'status'      => 'nullable|string|in:confirmed,tentative,cancelled',
        ]);

        $event->update($data);

        return response()->json(['ok' => true]);
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->authorizeEvent($event);
        $event->delete();

        return response()->json(['ok' => true]);
    }

    public function storeCalendar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'required|string|max:7',
        ]);

        Calendar::create($data + [
            'user_id'    => auth()->id(),
            'is_default' => false,
        ]);

        return back()->with('success', 'Calendrier créé.');
    }

    public function destroyCalendar(Calendar $calendar): RedirectResponse
    {
        abort_unless($calendar->user_id === auth()->id(), 403);

        if ($calendar->is_default) {
            return back()->with('error', 'Impossible de supprimer le calendrier par défaut.');
        }

        $calendar->delete();

        return back()->with('success', 'Calendrier supprimé.');
    }

    private function authorizeCalendar(int $calendarId): void
    {
        abort_unless(
            Calendar::where('id', $calendarId)->where('user_id', auth()->id())->exists(),
            403
        );
    }

    private function authorizeEvent(Event $event): void
    {
        abort_unless($event->user_id === auth()->id(), 403);
    }
}
