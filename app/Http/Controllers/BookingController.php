<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\RoomCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function create(): View
    {
        return view('booking.create', [
            'categories' => RoomCategory::all(),
        ]);
    }

    /**
     * BL-015. DR-003 (unique ref_no), DR-004/DR-005 (customer dedup/create,
     * same pattern as CustomerController::store()), DR-006 as corrected by
     * CQ-004 (category_id is FK-validated, not a free-text room_type with a
     * silent Deluxe fallback), CQ-011 (booking.customer_id FK populated).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $booking = DB::transaction(function () use ($validated) {
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

            return Booking::create([
                'ref_no' => Booking::generateUniqueRefNo(),
                'customer_id' => $customer->id,
                'name' => $validated['name'],
                'mail' => $validated['mail'],
                'phone' => $validated['phone'],
                'category_id' => $validated['category_id'],
                'adult' => $validated['adult'],
                'children' => $validated['children'],
                'datein' => $validated['datein'],
                'dateout' => $validated['dateout'],
                'days_of_stay' => Booking::computeDaysOfStay($validated['datein'], $validated['dateout']),
                'status' => Booking::STATUS_BOOKED,
                'message' => $validated['message'] ?? null,
            ]);
        });

        return redirect()->route('booking.create')->with('ref_no', $booking->ref_no);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mail' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9]{7,15}$/'],
            'category_id' => ['required', 'integer', 'exists:room_categories,id'],
            'adult' => ['required', 'integer', 'min:0'],
            'children' => ['required', 'integer', 'min:0'],
            'datein' => ['required', 'date'],
            'dateout' => ['required', 'date', 'after_or_equal:datein'],
            'message' => ['nullable', 'string'],
        ]);
    }
}
