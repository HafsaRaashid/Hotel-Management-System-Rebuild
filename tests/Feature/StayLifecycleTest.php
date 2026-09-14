<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-004 (BL-020/BL-021/BL-022/BL-023). AC-N references are to
 * .specclaw/changes/005-booking-stay-lifecycle/spec.md.
 */
class StayLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // BL-011 (change 007-admin-authentication): every admin route now
        // requires an authenticated session. Acts as the seeded admin user
        // (see database/migrations/2026_09_14_000006_create_users_table.php).
        $this->actingAs(User::first());
    }

    private function makeCustomer(array $overrides = []): Customer
    {
        return Customer::create(array_merge([
            'customer_id' => Customer::generateUniqueCustomerId(),
            'name' => 'Jane Doe',
            'mail' => 'jane@example.com',
            'phone' => '5551234567',
            'address' => '1 Test St',
            'charges' => 0,
        ], $overrides));
    }

    private function makeCheckedInBooking(array $overrides = []): Booking
    {
        $category = RoomCategory::where('name', 'Single Room')->firstOrFail(); // price 99
        $room = Room::create(['room' => 'Single_101', 'category_id' => $category->id, 'status' => 1]);

        return Booking::create(array_merge([
            'ref_no' => Booking::generateUniqueRefNo(),
            'customer_id' => $this->makeCustomer()->id,
            'name' => 'Jane Doe',
            'mail' => 'jane@example.com',
            'phone' => '5551234567',
            'category_id' => $category->id,
            'room_id' => $room->id,
            'adult' => 2,
            'children' => 0,
            'datein' => '2026-10-01',
            'dateout' => '2026-10-03', // 2 days
            'days_of_stay' => 2,
            'status' => Booking::STATUS_CHECKED_IN,
            'message' => null,
        ], $overrides));
    }

    public function test_stay_list_shows_only_checked_in_and_checked_out(): void
    {
        $this->makeCheckedInBooking(['ref_no' => 111, 'status' => Booking::STATUS_CHECKED_IN]);
        $this->makeCheckedInBooking(['ref_no' => 222, 'status' => Booking::STATUS_CHECKED_OUT]);
        $this->makeCheckedInBooking(['ref_no' => 333, 'status' => Booking::STATUS_BOOKED, 'room_id' => null]);
        $this->makeCheckedInBooking(['ref_no' => 444, 'status' => Booking::STATUS_CANCELLED, 'room_id' => null]);

        $response = $this->get(route('stays.index'));

        $response->assertSee('111');
        $response->assertSee('222');
        $response->assertDontSee('333');
        $response->assertDontSee('444');
    }

    public function test_checkout_page_computes_days_and_amount_due(): void
    {
        $booking = $this->makeCheckedInBooking();

        $response = $this->get(route('stays.checkout.show', $booking));

        // Single Room price is 99 (seeded in change 003); 2 days => 198.
        $response->assertSee('99');
        $response->assertSee('198');
    }

    /**
     * Regression for change 006-delete-referential-integrity's verify
     * finding: before that change, bookings.customer_id's FK was RESTRICT,
     * so a booking's customer could never be deleted and $booking->customer
     * was always safe inside checkout(). That change relaxed the FK to
     * nullOnDelete() (so a customer referenced only by a checked-out/
     * cancelled booking can be deleted, per its own AC-6) - which means a
     * checkout() reached a second time on a booking whose customer_id is
     * now null (a replay, a double submit, a direct POST) must be rejected
     * before touching $booking->customer, not crash on a null dereference.
     */
    public function test_checkout_on_already_checked_out_booking_with_null_customer_is_rejected_not_crashed(): void
    {
        $booking = $this->makeCheckedInBooking([
            'status' => Booking::STATUS_CHECKED_OUT,
            'customer_id' => null,
        ]);

        $response = $this->post(route('stays.checkout', $booking), ['payment' => 198]);

        $response->assertRedirect(route('stays.index'));
        $response->assertSessionHas('error');

        $booking->refresh();
        $this->assertSame(Booking::STATUS_CHECKED_OUT, $booking->status);
    }

    public function test_checkout_show_on_non_checked_in_booking_redirects_instead_of_rendering(): void
    {
        $booking = $this->makeCheckedInBooking(['status' => Booking::STATUS_CANCELLED, 'room_id' => null]);

        $this->get(route('stays.checkout.show', $booking))
            ->assertRedirect(route('stays.index'));
    }

    public function test_checkout_rejects_payment_below_amount_due(): void
    {
        $booking = $this->makeCheckedInBooking();

        $this->post(route('stays.checkout', $booking), ['payment' => 100])
            ->assertSessionHasErrors('payment');

        $booking->refresh();
        $this->assertSame(Booking::STATUS_CHECKED_IN, $booking->status);
    }

    public function test_checkout_succeeds_and_has_all_three_effects(): void
    {
        $booking = $this->makeCheckedInBooking();
        $customerChargesBefore = $booking->customer->charges;
        $roomId = $booking->room_id;

        $this->post(route('stays.checkout', $booking), ['payment' => 198])
            ->assertRedirect(route('stays.index'));

        $booking->refresh();
        $this->assertSame(Booking::STATUS_CHECKED_OUT, $booking->status);
        $this->assertSame(198, $booking->price);

        $this->assertSame($customerChargesBefore + 198, $booking->customer->fresh()->charges);

        $this->assertSame(0, Room::find($roomId)->status);
    }

    public function test_edit_date_recomputes_and_persists_days_of_stay(): void
    {
        $booking = $this->makeCheckedInBooking(['dateout' => '2026-10-03', 'days_of_stay' => 2]);

        $this->put(route('stays.update', $booking), ['dateout' => '2026-10-06'])
            ->assertRedirect(route('stays.index'));

        $booking->refresh();
        $this->assertSame('2026-10-06', $booking->dateout->format('Y-m-d'));
        $this->assertSame(5, $booking->days_of_stay);
    }

    public function test_view_completed_stay_renders_expected_fields(): void
    {
        $booking = $this->makeCheckedInBooking(['status' => Booking::STATUS_CHECKED_OUT, 'price' => 198]);

        $response = $this->get(route('stays.show', $booking));

        $response->assertStatus(200);
        $response->assertSee((string) $booking->ref_no);
        $response->assertSee($booking->name);
        $response->assertSee($booking->phone);
        $response->assertSee('198');
    }

    public function test_checkout_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $booking = $this->makeCheckedInBooking();

        $this->post(route('stays.checkout', $booking), ['payment' => 198])->assertStatus(419);

        $booking->refresh();
        $this->assertSame(Booking::STATUS_CHECKED_IN, $booking->status);
    }

    public function test_edit_date_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $booking = $this->makeCheckedInBooking(['dateout' => '2026-10-03', 'days_of_stay' => 2]);

        $this->put(route('stays.update', $booking), ['dateout' => '2026-10-06'])->assertStatus(419);

        $booking->refresh();
        $this->assertSame(2, $booking->days_of_stay);
    }

    public function test_days_of_stay_formula_matches_legacy(): void
    {
        // DR-009's exact legacy formula: floor(abs(strtotime(out) - strtotime(in)) / 86400).
        $this->assertSame(4, Booking::computeDaysOfStay('2026-10-01', '2026-10-05'));
    }
}
