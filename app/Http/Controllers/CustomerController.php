<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('customers.index', [
            'customers' => Customer::all(),
        ]);
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        if (Customer::where('phone', $validated['phone'])->exists()) {
            return redirect()
                ->route('customers.create')
                ->withInput()
                ->with('error', 'A customer with that phone number already exists.');
        }

        Customer::create([
            ...$validated,
            'customer_id' => Customer::generateUniqueCustomerId(),
        ]);

        return redirect()->route('customers.index');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', ['customer' => $customer]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validated($request));

        return redirect()->route('customers.index');
    }

    /**
     * CQ-021: the legacy handler's delete was a silent no-op (bind_param
     * defect) - this must actually delete. CQ-024: reject deleting a
     * customer still referenced by an active (status 0=booked or
     * 1=checked_in, per that decision's own "not checked-out/cancelled"
     * wording) booking - checked via the customer_id FK, not phone. This
     * check was deferred when this change first shipped (see
     * .specclaw/changes/002-customer-management/spec.md's Item Split) until
     * the `bookings` table existed; it now does (change
     * 005-booking-stay-lifecycle), closed here in change
     * 006-delete-referential-integrity.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $hasActiveBooking = Booking::where('customer_id', $customer->id)
            ->whereIn('status', [Booking::STATUS_BOOKED, Booking::STATUS_CHECKED_IN])
            ->exists();

        if ($hasActiveBooking) {
            return redirect()
                ->route('customers.index')
                ->with('error', 'This customer is referenced by an active booking and cannot be deleted.');
        }

        $customer->delete();

        return redirect()->route('customers.index');
    }

    /**
     * DR-005's phone-dedup check applies only to create (store), not update -
     * the rule is about creating a new record, not editing an existing one.
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mail' => ['required', 'email', 'max:255'],
            // CQ-012: real phone-number format validation. No format is pinned
            // by the source decision - digits only, 7-15 chars, per spec.md's
            // stated assumption (NFR-2).
            'phone' => ['required', 'regex:/^[0-9]{7,15}$/'],
            'address' => ['required', 'string', 'max:255'],
        ]);
    }
}
