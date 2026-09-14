<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\RoomCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-005 (BL-006/BL-007/BL-008). AC-N references are to
 * .specclaw/changes/002-customer-management/spec.md.
 *
 * Only the AC-8 (CSRF) tests override app('env') to 'production' - every
 * other test relies on the default APP_ENV=testing bypass (see
 * tests/Feature/CsrfProtectionTest.php's own comment on this), so none of
 * them need to carry a token.
 */
class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomer(array $overrides = []): Customer
    {
        return Customer::create(array_merge([
            'customer_id' => Customer::generateUniqueCustomerId(),
            'name' => 'Jane Doe',
            'mail' => 'jane@example.com',
            'phone' => '5551234567',
            'address' => '123 Main St',
            'charges' => 0,
        ], $overrides));
    }

    public function test_list_shows_all_customer_fields(): void
    {
        $customer = $this->makeCustomer(['charges' => 42]);

        $response = $this->get(route('customers.index'));

        $response->assertStatus(200);
        $response->assertSee($customer->customer_id);
        $response->assertSee('Jane Doe');
        $response->assertSee('jane@example.com');
        $response->assertSee('5551234567');
        $response->assertSee('123 Main St');
        $response->assertSee('42');
    }

    public function test_create_assigns_unique_customer_ids_across_many_creates(): void
    {
        $ids = [];

        for ($i = 0; $i < 50; $i++) {
            $this->post(route('customers.store'), [
                'name' => 'Customer '.$i,
                'mail' => "customer{$i}@example.com",
                'phone' => str_pad((string) (5550000000 + $i), 10, '0', STR_PAD_LEFT),
                'address' => '1 Test St',
            ])->assertRedirect(route('customers.index'));

            $ids[] = Customer::where('mail', "customer{$i}@example.com")->firstOrFail()->customer_id;
        }

        foreach ($ids as $id) {
            $this->assertGreaterThanOrEqual(0, $id);
            $this->assertLessThanOrEqual(99999999, $id);
        }

        $this->assertCount(50, array_unique($ids), 'Every generated customer_id must be unique.');
    }

    public function test_create_skips_insert_when_phone_already_exists(): void
    {
        $this->makeCustomer(['phone' => '9998887777']);
        $this->assertSame(1, Customer::count());

        $this->post(route('customers.store'), [
            'name' => 'Duplicate Phone',
            'mail' => 'dup@example.com',
            'phone' => '9998887777',
            'address' => '456 Side St',
        ])->assertRedirect(route('customers.create'));

        $this->assertSame(1, Customer::count(), 'No second row should be inserted for a duplicate phone.');
    }

    public function test_create_rejects_invalid_phone_format(): void
    {
        $this->post(route('customers.store'), [
            'name' => 'Bad Phone',
            'mail' => 'bad@example.com',
            'phone' => 'not-a-phone',
            'address' => '1 Test St',
        ])->assertSessionHasErrors('phone');

        $this->assertSame(0, Customer::count());
    }

    public function test_create_accepts_valid_phone(): void
    {
        $this->post(route('customers.store'), [
            'name' => 'Good Phone',
            'mail' => 'good@example.com',
            'phone' => '5559998888',
            'address' => '1 Test St',
        ])->assertRedirect(route('customers.index'));

        $this->assertSame(1, Customer::count());
    }

    public function test_edit_updates_fields_without_changing_customer_id(): void
    {
        $customer = $this->makeCustomer();
        $originalCustomerId = $customer->customer_id;

        $this->put(route('customers.update', $customer), [
            'name' => 'Jane Updated',
            'mail' => 'jane.updated@example.com',
            'phone' => '5551234567',
            'address' => '999 New St',
        ])->assertRedirect(route('customers.index'));

        $customer->refresh();

        $this->assertSame('Jane Updated', $customer->name);
        $this->assertSame('jane.updated@example.com', $customer->mail);
        $this->assertSame('999 New St', $customer->address);
        $this->assertSame($originalCustomerId, $customer->customer_id);
    }

    /**
     * AC-4 (.specclaw/changes/006-delete-referential-integrity/spec.md) -
     * regression check: deleting a customer with no active-booking
     * reference still works (CQ-021). The old test_controller_source_
     * contains_no_booking_reference guard from change 002 is gone - it
     * proved BL-008's CQ-024 deferral was a genuine absence; now that the
     * deferral is over (this change closes it), a Booking reference is
     * correct and expected, not a defect to guard against.
     */
    public function test_delete_removes_unreferenced_customer(): void
    {
        $customer = $this->makeCustomer();

        $this->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    private function makeBookingFor(Customer $customer, int $status): Booking
    {
        $category = RoomCategory::first();
        $room = Room::create(['room' => 'Single_101', 'category_id' => $category->id, 'status' => 0]);

        return Booking::create([
            'ref_no' => Booking::generateUniqueRefNo(),
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'mail' => $customer->mail,
            'phone' => $customer->phone,
            'category_id' => $category->id,
            'room_id' => $room->id,
            'adult' => 1,
            'children' => 0,
            'datein' => '2026-10-01',
            'dateout' => '2026-10-03',
            'days_of_stay' => 2,
            'status' => $status,
        ]);
    }

    public function test_delete_rejects_customer_referenced_by_active_booking(): void
    {
        $customer = $this->makeCustomer();
        $this->makeBookingFor($customer, Booking::STATUS_CHECKED_IN);

        $this->delete(route('customers.destroy', $customer))->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_delete_allows_customer_referenced_only_by_checked_out_or_cancelled_booking(): void
    {
        $customer = $this->makeCustomer();
        $this->makeBookingFor($customer, Booking::STATUS_CHECKED_OUT);

        $this->delete(route('customers.destroy', $customer))->assertRedirect(route('customers.index'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_store_without_csrf_token_is_rejected_before_handler_runs(): void
    {
        $this->app['env'] = 'production';

        $this->post(route('customers.store'), [
            'name' => 'No Token',
            'mail' => 'notoken@example.com',
            'phone' => '5551112222',
            'address' => '1 Test St',
        ])->assertStatus(419);

        $this->assertSame(0, Customer::count());
    }

    public function test_destroy_without_csrf_token_is_rejected_before_handler_runs(): void
    {
        $this->app['env'] = 'production';

        $customer = $this->makeCustomer();

        $this->delete(route('customers.destroy', $customer))->assertStatus(419);

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }
}
