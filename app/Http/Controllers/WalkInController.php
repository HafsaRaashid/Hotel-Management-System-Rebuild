<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WalkInController extends Controller
{
    /**
     * BL-018. Enumeration 2: rooms.status 0 = available.
     */
    public function available(): View
    {
        return view('walk-in.available', [
            'rooms' => Room::where('status', 0)->with('category')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $room = Room::where('status', 0)->findOrFail($request->query('room_id'));

        return view('walk-in.create', ['room' => $room]);
    }

    /**
     * BL-019. DR-003/DR-004/DR-005/DR-006(-as-corrected-by-CQ-004)/CQ-011/CQ-012:
     * same generation/dedup/validation mechanisms as BookingController::store(),
     * independently implemented for this handler (module-map.md notes the
     * legacy app duplicates this logic per call site). DR-008: the booking is
     * created already checked-in (status=1), skipping the 'booked' stage.
     * No CQ-022 handling needed - see design.md's Key Decisions (moot in
     * Eloquent, which has no manual connection lifecycle to mismanage).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $room = Room::where('status', 0)->findOrFail($validated['room_id']);

        DB::transaction(function () use ($validated, $room) {
            $customer = Customer::where('phone', $validated['phone'])->first();

            if (! $customer) {
                $customer = Customer::create([
                    'customer_id' => Customer::generateUniqueCustomerId(),
                    'name' => $validated['name'],
                    'mail' => $validated['mail'],
                    'phone' => $validated['phone'],
                    'address' => '',
                    'charges' => 0,
                ]);
            }

            Booking::create([
                'ref_no' => Booking::generateUniqueRefNo(),
                'customer_id' => $customer->id,
                'name' => $validated['name'],
                'mail' => $validated['mail'],
                'phone' => $validated['phone'],
                'category_id' => $room->category_id,
                'room_id' => $room->id,
                'adult' => $validated['adult'],
                'children' => $validated['children'],
                'datein' => $validated['datein'],
                'dateout' => $validated['dateout'],
                'days_of_stay' => Booking::computeDaysOfStay($validated['datein'], $validated['dateout']),
                'status' => Booking::STATUS_CHECKED_IN,
                'message' => $validated['message'] ?? null,
            ]);

            $room->update(['status' => 1]);
        });

        return redirect()->route('walk-in.available');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mail' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9]{7,15}$/'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'adult' => ['required', 'integer', 'min:0'],
            'children' => ['required', 'integer', 'min:0'],
            'datein' => ['required', 'date'],
            'dateout' => ['required', 'date', 'after_or_equal:datein'],
            'message' => ['nullable', 'string'],
        ]);
    }
}
