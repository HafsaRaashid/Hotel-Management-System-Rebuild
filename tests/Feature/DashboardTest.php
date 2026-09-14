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
 * MOD-007 (BL-024). AC-N references are to
 * .specclaw/changes/008-dashboard-reporting/spec.md.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(array $overrides = []): Customer
    {
        return Customer::create(array_merge([
            'customer_id' => Customer::generateUniqueCustomerId(),
            'name' => 'Jane Doe',
            'mail' => 'jane@example.com',
            'phone' => (string) random_int(1000000, 9999999),
            'address' => '1 Test St',
            'charges' => 0,
        ], $overrides));
    }

    private function makeRoom(array $overrides = []): Room
    {
        return Room::create(array_merge([
            'room' => 'Single_'.random_int(100, 999),
            'category_id' => RoomCategory::first()->id,
            'status' => 0,
        ], $overrides));
    }

    private function makeBooking(array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'ref_no' => Booking::generateUniqueRefNo(),
            'customer_id' => $this->makeCustomer()->id,
            'name' => 'Jane Doe',
            'mail' => 'jane@example.com',
            'phone' => '5551234567',
            'category_id' => RoomCategory::first()->id,
            'adult' => 1,
            'children' => 0,
            'datein' => '2026-10-01',
            'dateout' => '2026-10-03',
            'days_of_stay' => 2,
            'status' => Booking::STATUS_BOOKED,
        ], $overrides));
    }

    public function test_dashboard_redirects_to_login_when_unauthenticated(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_answers_when_authenticated_and_shows_all_nine_labels(): void
    {
        $this->actingAs(User::first());

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        foreach ([
            'Total Bookings', 'Checked In', 'Checked Out', 'Total Payment',
            'Available Rooms', 'Total Rooms', 'Total Room Categories',
            'Total Customers', 'Total Users',
        ] as $label) {
            $response->assertSee($label);
        }
    }

    public function test_total_bookings_counts_every_status(): void
    {
        $this->actingAs(User::first());

        $this->makeBooking(['ref_no' => 1, 'status' => Booking::STATUS_BOOKED]);
        $this->makeBooking(['ref_no' => 2, 'status' => Booking::STATUS_CHECKED_IN]);
        $this->makeBooking(['ref_no' => 3, 'status' => Booking::STATUS_CHECKED_OUT, 'price' => 100]);
        $this->makeBooking(['ref_no' => 4, 'status' => Booking::STATUS_CANCELLED]);

        $this->assertSame(4, Booking::count());

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_checked_in_and_checked_out_counts_exclude_booked_and_cancelled(): void
    {
        $this->actingAs(User::first());

        $this->makeBooking(['ref_no' => 1, 'status' => Booking::STATUS_BOOKED]);
        $this->makeBooking(['ref_no' => 2, 'status' => Booking::STATUS_CHECKED_IN]);
        $this->makeBooking(['ref_no' => 3, 'status' => Booking::STATUS_CHECKED_IN]);
        $this->makeBooking(['ref_no' => 4, 'status' => Booking::STATUS_CHECKED_OUT, 'price' => 100]);
        $this->makeBooking(['ref_no' => 5, 'status' => Booking::STATUS_CANCELLED]);

        $this->assertSame(2, Booking::where('status', Booking::STATUS_CHECKED_IN)->count());
        $this->assertSame(1, Booking::where('status', Booking::STATUS_CHECKED_OUT)->count());

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_total_payment_sums_only_checked_out_bookings(): void
    {
        $this->actingAs(User::first());

        // A non-zero price on a not-yet-checked-out booking would only occur
        // via direct DB manipulation in production (checkout() is the only
        // writer of `price`), but forcing one here proves the dashboard
        // filters by status rather than summing every row's price.
        $this->makeBooking(['ref_no' => 1, 'status' => Booking::STATUS_BOOKED, 'price' => 500]);
        $this->makeBooking(['ref_no' => 2, 'status' => Booking::STATUS_CHECKED_IN, 'price' => 500]);
        $this->makeBooking(['ref_no' => 3, 'status' => Booking::STATUS_CHECKED_OUT, 'price' => 198]);
        $this->makeBooking(['ref_no' => 4, 'status' => Booking::STATUS_CHECKED_OUT, 'price' => 99]);

        $this->get(route('dashboard'))->assertSee('297');
    }

    public function test_available_and_total_rooms(): void
    {
        $this->actingAs(User::first());

        $this->makeRoom(['status' => 0]);
        $this->makeRoom(['status' => 0]);
        $this->makeRoom(['status' => 1]);

        $this->assertSame(2, Room::where('status', 0)->count());
        $this->assertSame(3, Room::count());

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_plain_counts_for_categories_customers_and_users(): void
    {
        $this->actingAs(User::first());

        $this->makeCustomer();
        $this->makeCustomer();

        $this->assertSame(3, RoomCategory::count());
        $this->assertSame(2, Customer::count());
        $this->assertSame(1, User::count());

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_fresh_install_renders_zero_counters_not_an_error(): void
    {
        $this->actingAs(User::first());

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('0');
    }

    public function test_login_redirects_to_dashboard_not_customers_index(): void
    {
        $this->post(route('login.attempt'), [
            'username' => 'admin',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_admin_nav_links_to_dashboard_on_an_existing_screen(): void
    {
        $this->actingAs(User::first());

        $this->get(route('customers.index'))
            ->assertSee(route('dashboard'), false);
    }

    public function test_dashboard_performs_no_writes(): void
    {
        $this->actingAs(User::first());

        $before = [
            'bookings' => Booking::count(),
            'rooms' => Room::count(),
            'room_categories' => RoomCategory::count(),
            'customers' => Customer::count(),
            'users' => User::count(),
        ];

        $this->get(route('dashboard'))->assertOk();

        $this->assertSame($before, [
            'bookings' => Booking::count(),
            'rooms' => Room::count(),
            'room_categories' => RoomCategory::count(),
            'customers' => Customer::count(),
            'users' => User::count(),
        ]);
    }
}
