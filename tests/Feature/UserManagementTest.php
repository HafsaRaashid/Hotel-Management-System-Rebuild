<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * MOD-002 (BL-012/BL-013/BL-014). AC-N references are to
 * .specclaw/changes/009-user-account-management/spec.md.
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // BL-011 (change 007-admin-authentication): every admin route
        // requires an authenticated session. Acts as the seeded admin user
        // (see database/migrations/2026_09_14_000006_create_users_table.php).
        $this->actingAs(User::first());
    }

    private function makeStaff(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Staff Member',
            'username' => 'staffmember',
            'password' => 'password123',
            'type' => User::TYPE_STAFF,
        ], $overrides));
    }

    public function test_list_shows_every_user_with_distinguishable_type(): void
    {
        $this->makeStaff();

        $response = $this->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertSee('Admin');
        $response->assertSee('admin');
        $response->assertSee('Staff');
        $response->assertSee('staffmember');
    }

    public function test_create_as_admin_succeeds(): void
    {
        $this->post(route('users.store'), [
            'name' => 'New User',
            'username' => 'newuser',
            'password' => 'password123',
            'type' => User::TYPE_STAFF,
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['username' => 'newuser']);
    }

    public function test_create_rejects_duplicate_username(): void
    {
        $this->post(route('users.store'), [
            'name' => 'New User',
            'username' => 'admin',
            'password' => 'password123',
            'type' => User::TYPE_STAFF,
        ])->assertSessionHasErrors('username');

        $this->assertSame(1, User::where('username', 'admin')->count());
    }

    public function test_create_as_non_admin_is_rejected(): void
    {
        $this->actingAs($this->makeStaff());

        $this->post(route('users.store'), [
            'name' => 'Blocked User',
            'username' => 'blockeduser',
            'password' => 'password123',
            'type' => User::TYPE_STAFF,
        ])->assertStatus(403);

        $this->assertDatabaseMissing('users', ['username' => 'blockeduser']);
    }

    public function test_edit_form_renders_password_field_blank(): void
    {
        $user = $this->makeStaff();

        $response = $this->get(route('users.edit', $user));

        $response->assertStatus(200);
        // DR-013: the legacy defect pre-filled the password input's value
        // with the user's id (`value="<?php echo $get['id'] ?>"`) - assert
        // that value attribute never appears, rather than checking for the
        // id string anywhere on the page (it legitimately appears in the
        // form's own action URL, e.g. /users/{id}).
        $response->assertDontSee('value="'.$user->id.'"', false);
        $response->assertDontSee($user->password, false);
    }

    public function test_update_with_blank_password_leaves_it_unchanged(): void
    {
        $user = $this->makeStaff();

        $this->put(route('users.update', $user), [
            'name' => 'Staff Updated',
            'username' => $user->username,
            'password' => '',
            'type' => User::TYPE_STAFF,
        ])->assertRedirect(route('users.index'));

        $user->refresh();

        $this->assertSame('Staff Updated', $user->name);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_update_with_new_password_replaces_it(): void
    {
        $user = $this->makeStaff();

        $this->put(route('users.update', $user), [
            'name' => $user->name,
            'username' => $user->username,
            'password' => 'newpassword456',
            'type' => User::TYPE_STAFF,
        ])->assertRedirect(route('users.index'));

        $user->refresh();

        $this->assertTrue(Hash::check('newpassword456', $user->password));
        $this->assertFalse(Hash::check('password123', $user->password));
    }

    public function test_update_as_non_admin_is_rejected(): void
    {
        $target = $this->makeStaff();
        $this->actingAs($this->makeStaff(['username' => 'otherstaff']));

        $this->put(route('users.update', $target), [
            'name' => 'Should Not Apply',
            'username' => $target->username,
            'password' => '',
            'type' => User::TYPE_STAFF,
        ])->assertStatus(403);

        $target->refresh();
        $this->assertSame('Staff Member', $target->name);
    }

    public function test_delete_as_admin_removes_the_user(): void
    {
        $user = $this->makeStaff();

        $this->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_delete_as_non_admin_is_rejected(): void
    {
        $target = $this->makeStaff();
        $this->actingAs($this->makeStaff(['username' => 'otherstaff']));

        $this->delete(route('users.destroy', $target))->assertStatus(403);

        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_store_without_csrf_token_is_rejected_before_handler_runs(): void
    {
        $this->app['env'] = 'production';

        $this->post(route('users.store'), [
            'name' => 'No Token',
            'username' => 'notoken',
            'password' => 'password123',
            'type' => User::TYPE_STAFF,
        ])->assertStatus(419);

        $this->assertDatabaseMissing('users', ['username' => 'notoken']);
    }

    public function test_update_without_csrf_token_is_rejected_before_handler_runs(): void
    {
        $this->app['env'] = 'production';
        $user = $this->makeStaff();

        $this->put(route('users.update', $user), [
            'name' => 'Should Not Apply',
            'username' => $user->username,
            'password' => '',
            'type' => User::TYPE_STAFF,
        ])->assertStatus(419);

        $user->refresh();
        $this->assertSame('Staff Member', $user->name);
    }

    public function test_destroy_without_csrf_token_is_rejected_before_handler_runs(): void
    {
        $this->app['env'] = 'production';
        $user = $this->makeStaff();

        $this->delete(route('users.destroy', $user))->assertStatus(419);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
