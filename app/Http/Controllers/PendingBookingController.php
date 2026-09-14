<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PendingBookingController extends Controller
{
    /**
     * BL-016. Booking.status: 0 = booked (pending).
     */
    public function index(): View
    {
        return view('bookings.pending', [
            'bookings' => Booking::where('status', Booking::STATUS_BOOKED)->get(),
        ]);
    }

    /**
     * CQ-014: cancellation sets a 'cancelled' status instead of deleting the
     * row, so the record remains queryable - never $booking->delete().
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        return redirect()->route('bookings.pending');
    }

    /**
     * BL-017. FR-10: only available rooms in the booking's own category may
     * be assigned - grounded in the Room/RoomCategory relationship itself
     * (see design.md's Key Decisions), not a separate DR-### citation.
     */
    public function showConvert(Booking $booking): View
    {
        return view('bookings.convert', [
            'booking' => $booking,
            'rooms' => Room::where('status', 0)->where('category_id', $booking->category_id)->get(),
        ]);
    }

    /**
     * DR-007: converting a pending booking to checked-in assigns a specific
     * room, occupies it, and flips the booking - one composite write. The
     * chosen room's availability and category are re-checked server-side,
     * not just trusted from what the GET listed.
     */
    public function convert(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => [
                'required',
                'integer',
                Rule::exists('rooms', 'id')
                    ->where('status', 0)
                    ->where('category_id', $booking->category_id),
            ],
        ]);

        $room = Room::findOrFail($validated['room_id']);

        DB::transaction(function () use ($booking, $room) {
            $booking->update([
                'status' => Booking::STATUS_CHECKED_IN,
                'room_id' => $room->id,
            ]);

            $room->update(['status' => 1]);
        });

        return redirect()->route('bookings.pending');
    }
}
