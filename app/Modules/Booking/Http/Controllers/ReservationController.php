<?php

namespace App\Modules\Booking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Booking;
use App\Modules\Booking\Models\Resource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(): View
    {
        $bookings   = Booking::with(['resource.category', 'user', 'approvedBy'])
                        ->orderByDesc('start_at')
                        ->paginate(20);
        $resources  = Resource::active()->orderBy('name')->get(['id', 'name', 'requires_approval']);

        return view('modules.booking.reservations.index', compact('bookings', 'resources'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'resource_id'    => 'required|exists:booking_resources,id',
            'title'          => 'required|string|max:191',
            'description'    => 'nullable|string',
            'start_at'       => 'required|date',
            'end_at'         => 'required|date|after:start_at',
            'attendees_count'=> 'required|integer|min:1',
        ]);

        $conflicts = $this->detectConflicts(null, $data['resource_id'], $data['start_at'], $data['end_at']);
        if ($conflicts && !$request->boolean('force')) {
            return response()->json(['conflicts' => $conflicts], 409);
        }

        $data['user_id']          = auth()->id();
        $data['duration_minutes'] = (int) round(
            (strtotime($data['end_at']) - strtotime($data['start_at'])) / 60
        );

        $resource       = Resource::find($data['resource_id']);
        $data['status'] = $resource->requires_approval ? 'pending' : 'confirmed';

        if ($data['status'] === 'confirmed') {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
        }

        $booking = Booking::create($data);
        $booking->load(['resource', 'user']);

        return response()->json([
            'id'              => $booking->id,
            'title'           => $booking->title . ' — ' . $booking->user->name,
            'start'           => $booking->start_at->toIso8601String(),
            'end'             => $booking->end_at->toIso8601String(),
            'backgroundColor' => $booking->status === 'pending' ? '#F59E0B' : ($booking->resource->color ?? '#EA580C'),
            'borderColor'     => $booking->status === 'pending' ? '#F59E0B' : ($booking->resource->color ?? '#EA580C'),
        ], 201);
    }

    public function update(Request $request, Booking $booking): JsonResponse
    {
        $data = $request->validate([
            'title'          => 'sometimes|required|string|max:191',
            'description'    => 'nullable|string',
            'start_at'       => 'sometimes|required|date',
            'end_at'         => 'sometimes|required|date|after:start_at',
            'attendees_count'=> 'sometimes|required|integer|min:1',
            'resource_id'    => 'sometimes|required|exists:booking_resources,id',
        ]);

        if (isset($data['start_at'], $data['end_at'])) {
            $resourceId = $data['resource_id'] ?? $booking->resource_id;
            $conflicts  = $this->detectConflicts($booking->id, $resourceId, $data['start_at'], $data['end_at']);
            if ($conflicts && !$request->boolean('force')) {
                return response()->json(['conflicts' => $conflicts], 409);
            }
            $data['duration_minutes'] = (int) round(
                (strtotime($data['end_at']) - strtotime($data['start_at'])) / 60
            );
        }

        $booking->update($data);
        return response()->json(['ok' => true]);
    }

    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();
        return response()->json(['ok' => true]);
    }

    public function approve(Booking $booking): JsonResponse
    {
        $booking->update([
            'status'      => 'confirmed',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return response()->json(['ok' => true]);
    }

    public function reject(Request $request, Booking $booking): JsonResponse
    {
        $data = $request->validate(['rejection_reason' => 'nullable|string']);

        $booking->update([
            'status'           => 'rejected',
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);
        return response()->json(['ok' => true]);
    }

    public function cancel(Booking $booking): JsonResponse
    {
        $booking->update(['status' => 'cancelled']);
        return response()->json(['ok' => true]);
    }

    private function detectConflicts(?int $excludeId, int $resourceId, string $start, string $end): array
    {
        $query = Booking::where('resource_id', $resourceId)
            ->where('start_at', '<', $end)
            ->where('end_at', '>', $start)
            ->whereIn('status', ['pending', 'confirmed']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            return [['type' => 'overlap', 'message' => 'Cette ressource est déjà réservée sur ce créneau.']];
        }

        return [];
    }
}
