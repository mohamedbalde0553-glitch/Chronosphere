<?php

namespace App\Modules\Booking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Models\Resource;
use App\Modules\Booking\Models\ResourceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        $stats = [
            'resources'       => Resource::active()->count(),
            'categories'      => ResourceCategory::count(),
            'bookings_week'   => Booking::inRange(
                                    now()->startOfWeek()->toDateTimeString(),
                                    now()->endOfWeek()->toDateTimeString()
                                )->whereIn('status', ['pending', 'confirmed'])->count(),
            'pending_approval'=> Booking::where('status', 'pending')->count(),
        ];

        $upcomingBookings = Booking::with(['resource.category', 'user'])
            ->where('start_at', '>=', now())
            ->where('start_at', '<=', now()->addDays(7))
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('start_at')
            ->limit(5)
            ->get();

        $pendingBookings = Booking::with(['resource', 'user'])
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $resources = Resource::active()->with('category')->orderBy('name')->get();

        return view('modules.booking.index', compact(
            'stats', 'upcomingBookings', 'pendingBookings', 'resources'
        ));
    }

    public function calendar(Request $request): View
    {
        $resources  = Resource::active()->with('category')->orderBy('name')->get();
        $categories = ResourceCategory::orderBy('name')->get();

        $filterType = $request->get('by', 'resource');
        $filterId   = $request->get('id', $resources->first()?->id);

        return view('modules.booking.calendar', compact(
            'resources', 'categories', 'filterType', 'filterId'
        ));
    }

    public function feed(Request $request): JsonResponse
    {
        $start      = $request->query('start', now()->startOfWeek()->toDateTimeString());
        $end        = $request->query('end', now()->endOfWeek()->toDateTimeString());
        $filterType = $request->query('by', 'resource');
        $filterId   = $request->query('id');

        $query = Booking::with(['resource', 'user'])
            ->inRange($start, $end)
            ->whereNotIn('status', ['cancelled', 'rejected']);

        if ($filterId) {
            match ($filterType) {
                'resource' => $query->where('resource_id', $filterId),
                'category' => $query->whereHas('resource', fn ($q) => $q->where('category_id', $filterId)),
                default    => null,
            };
        }

        $statusColors = [
            'confirmed' => null,
            'pending'   => '#F59E0B',
        ];

        return response()->json($query->get()->map(fn (Booking $b) => [
            'id'              => $b->id,
            'title'           => $b->title . ' — ' . $b->user->name,
            'start'           => $b->start_at->toIso8601String(),
            'end'             => $b->end_at->toIso8601String(),
            'backgroundColor' => $statusColors[$b->status] ?? ($b->resource->color ?? '#EA580C'),
            'borderColor'     => $statusColors[$b->status] ?? ($b->resource->color ?? '#EA580C'),
            'opacity'         => $b->status === 'pending' ? 0.75 : 1,
            'extendedProps'   => [
                'resource_id'   => $b->resource_id,
                'resource_name' => $b->resource->name,
                'user_id'       => $b->user_id,
                'user_name'     => $b->user->name,
                'attendees'     => $b->attendees_count,
                'status'        => $b->status,
                'description'   => $b->description,
            ],
        ]));
    }
}
