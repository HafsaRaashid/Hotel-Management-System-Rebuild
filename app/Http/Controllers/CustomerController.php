<?php

namespace App\Http\Controllers;

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
     * defect) - this must actually delete. CQ-024's referential-integrity
     * check against active bookings is explicitly deferred (see
     * .specclaw/changes/002-customer-management/spec.md's Item Split) - no
     * `booking`/`Booking` construct exists in this repo yet, and none is
     * referenced here.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
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
