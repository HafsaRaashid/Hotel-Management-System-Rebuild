<?php

namespace App\Http\Controllers;

use App\Models\Booking;
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
}
