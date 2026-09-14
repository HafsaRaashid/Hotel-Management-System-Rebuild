<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\RoomCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-004 (BL-015). AC-N references are to
 * .specclaw/changes/005-booking-stay-lifecycle/spec.md.
 */
class BookingIntakeTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(array $overrides = []): array
    {
        $category = RoomCategory::first();

        return array_merge([
            'name' => 'Jane Doe',
            'mail' => 'jane@example.com',
            'phone' => '5551234567',
            'category_id' => $category->id,
            'adult' => 2,
            'children' => 0,
            'datein' => '2026-10-01',
            'dateout' => '2026-10-05',
            'message' => 'Late arrival',
        ], $overrides);
    }

    public function test_submitting_creates_booking_with_unique_ref_no(): void
    {
        $this->post(route('booking.store'), $this->validPayload())
            ->assertRedirect(route('booking.create'));

        $this->assertSame(1, Booking::count());
        $booking = Booking::first();
        $this->assertGreaterThanOrEqual(0, $booking->ref_no);
        $this->assertLessThanOrEqual(999999999, $booking->ref_no);
        $this->assertSame(0, $booking->status);
    }

    public function test_new_phone_creates_a_customer_and_links_it(): void
    {
        $this->assertSame(0, Customer::count());

        $this->post(route('booking.store'), $this->validPayload(['phone' => '5559990000']));

        $this->assertSame(1, Customer::count());
        $customer = Customer::first();
        $this->assertSame($customer->id, Booking::first()->customer_id);
    }

    public function test_existing_phone_reuses_customer_without_duplicating(): void
    {
        $customer = Customer::create([
            'customer_id' => Customer::generateUniqueCustomerId(),
            'name' => 'Existing Guest',
            'mail' => 'existing@example.com',
            'phone' => '5551112222',
            'address' => '1 Test St',
            'charges' => 0,
        ]);

        $this->post(route('booking.store'), $this->validPayload(['phone' => '5551112222']));

        $this->assertSame(1, Customer::count());
        $this->assertSame($customer->id, Booking::first()->customer_id);
    }

    public function test_nonexistent_category_id_is_rejected(): void
    {
        $this->post(route('booking.store'), $this->validPayload(['category_id' => 9999]))
            ->assertSessionHasErrors('category_id');

        $this->assertSame(0, Booking::count());
    }

    public function test_store_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $this->post(route('booking.store'), $this->validPayload())->assertStatus(419);

        $this->assertSame(0, Booking::count());
    }
}
