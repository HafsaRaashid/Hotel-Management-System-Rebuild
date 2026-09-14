<?php

namespace Tests\Feature;

use App\Models\Customer;
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

    public function test_delete_removes_customer(): void
    {
        $customer = $this->makeCustomer();

        $this->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_controller_source_contains_no_booking_reference(): void
    {
        // Strip comments first: this checks actual CODE has no Booking
        // construct (class reference, table name, query) - not that the
        // English word never appears in a docblock explaining the deferral.
        $source = file_get_contents(app_path('Http/Controllers/CustomerController.php'));
        $codeOnly = '';

        foreach (token_get_all($source) as $token) {
            if (is_array($token) && in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            $codeOnly .= is_array($token) ? $token[1] : $token;
        }

        $this->assertStringNotContainsStringIgnoringCase(
            'booking',
            $codeOnly,
            'CQ-024 is deferred (see spec.md Item Split) - the controller\'s actual code must not reference Booking/booking at all.'
        );
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
