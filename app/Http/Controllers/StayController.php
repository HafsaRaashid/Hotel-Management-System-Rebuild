<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StayController extends Controller
{
    /**
     * BL-020. Booking.status: 1 = checked_in, 2 = checked_out.
     */
    public function index(): View
    {
        return view('stays.index', [
            'stays' => Booking::whereIn('status', [Booking::STATUS_CHECKED_IN, Booking::STATUS_CHECKED_OUT])->get(),
        ]);
    }

    /**
     * BL-021. DR-009 (days of stay) and DR-010 (amount due = price x days)
     * computed here for display - checkout() below recomputes them itself
     * rather than trusting anything the client submits.
     */
    public function showCheckout(Booking $booking): View
    {
        $days = Booking::computeDaysOfStay($booking->datein, $booking->dateout);
        $amountDue = $booking->category->price * $days;

        return view('stays.checkout', [
            'booking' => $booking,
            'days' => $days,
            'amountDue' => $amountDue,
        ]);
    }

    /**
     * DR-010/CQ-005: the amount due is recomputed server-side (never trusted
     * from the client) and payment must be >= it. DR-011: closes the
     * booking, bills the customer via the customer_id FK (CQ-011, not
     * phone), and frees the room via its id (CQ-023, not name text) - one
     * composite write.
     */
    public function checkout(Request $request, Booking $booking): RedirectResponse
    {
        $days = Booking::computeDaysOfStay($booking->datein, $booking->dateout);
        $amountDue = $booking->category->price * $days;

        $validated = $request->validate([
            'payment' => ['required', 'integer', 'min:'.$amountDue],
        ]);

        DB::transaction(function () use ($booking, $validated) {
            $booking->update([
                'status' => Booking::STATUS_CHECKED_OUT,
                'price' => $validated['payment'],
            ]);

            $booking->customer->increment('charges', $validated['payment']);

            $booking->room->update(['status' => 0]);
        });

        return redirect()->route('stays.index');
    }
}
