<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * BL-024. Nine read-only counts/sums matching legacy admin/home.php's
     * admin/counters/*.php fragments. Total Users reads the `users` table
     * built in change 007-admin-authentication - see spec.md's Dependency
     * Note on why this doesn't require BL-012 (User List) to be built.
     */
    public function index(): View
    {
        return view('dashboard.index', [
            'totalBookings' => Booking::count(),
            'checkedIn' => Booking::where('status', Booking::STATUS_CHECKED_IN)->count(),
            'checkedOut' => Booking::where('status', Booking::STATUS_CHECKED_OUT)->count(),
            'totalPayment' => Booking::where('status', Booking::STATUS_CHECKED_OUT)->sum('price') ?? 0,
            'availableRooms' => Room::where('status', 0)->count(),
            'totalRooms' => Room::count(),
            'totalRoomCategories' => RoomCategory::count(),
            'totalCustomers' => Customer::count(),
            'totalUsers' => User::count(),
        ]);
    }
}
