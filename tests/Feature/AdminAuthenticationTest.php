<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MOD-001 (BL-010/BL-011). AC-N references are to
 * .specclaw/changes/007-admin-authentication/spec.md.
 *
 * The seeded admin user (username: admin, password: password123) comes
 * from database/migrations/2026_09_14_000006_create_users_table.php - see
 * that migration and spec.md's Notes for why these are documented
 * development-only placeholder credentials, not a security decision.
 */
class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN_ROUTES = [
        'customers.index',
        'rooms.index',
        'room-categories.index',
        'bookings.pending',
        'walk-in.available',
        'stays.index',
    ];

    public function test_login_with_blank_fields_is_rejected(): void
    {
        $this->post(route('login.attempt'), ['username' => '', 'password' => ''])
            ->assertSessionHasErrors(['username', 'password']);

        $this->assertGuest();
    }

    public function test_login_with_correct_credentials_succeeds(): void
    {
        // BL-024 (change 008-dashboard-reporting): login now lands on the
        // dashboard, not customers.index directly - see that change's
        // spec.md AC-8.
        $this->post(route('login.attempt'), [
            'username' => 'admin',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $this->followingRedirects()
            ->get(route('customers.index'))
            ->assertStatus(200);
    }

    public function test_login_with_wrong_password_is_rejected(): void
    {
        $this->post(route('login.attempt'), [
            'username' => 'admin',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    /**
     * CQ-020: the DB lookup is case-insensitive, but login itself must not
     * be - mirrors the legacy's own exact-case recheck.
     */
    public function test_login_with_wrong_case_username_is_rejected(): void
    {
        $this->post(route('login.attempt'), [
            'username' => 'Admin',
            'password' => 'password123',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_logout_ends_the_session(): void
    {
        $this->actingAs(User::first());

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();

        $this->get(route('customers.index'))->assertRedirect(route('login'));
    }

    public function test_every_admin_route_redirects_to_login_when_unauthenticated(): void
    {
        foreach (self::ADMIN_ROUTES as $routeName) {
            $this->get(route($routeName))->assertRedirect(route('login'));
        }
    }

    public function test_every_admin_route_answers_when_authenticated(): void
    {
        $this->actingAs(User::first());

        foreach (self::ADMIN_ROUTES as $routeName) {
            $this->get(route($routeName))->assertStatus(200);
        }
    }

    public function test_public_routes_remain_reachable_without_authentication(): void
    {
        foreach ([
            route('marketing.home'),
            route('marketing.room'),
            route('marketing.services'),
            route('marketing.food'),
            route('booking.create'),
        ] as $url) {
            $this->get($url)->assertStatus(200);
        }
    }

    public function test_admin_page_shows_links_to_every_admin_screen(): void
    {
        $this->actingAs(User::first());

        $response = $this->get(route('customers.index'));

        foreach (self::ADMIN_ROUTES as $routeName) {
            $response->assertSee(route($routeName), false);
        }
    }

    public function test_login_without_csrf_token_is_rejected(): void
    {
        $this->app['env'] = 'production';

        $this->post(route('login.attempt'), [
            'username' => 'admin',
            'password' => 'password123',
        ])->assertStatus(419);

        $this->assertGuest();
    }
}
