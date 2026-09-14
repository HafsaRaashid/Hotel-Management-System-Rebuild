<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
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
}
