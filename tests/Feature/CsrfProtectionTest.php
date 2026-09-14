<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * BL-001 — proves Laravel's default `ValidateCsrfToken` middleware (already
 * registered in the `web` group; see bootstrap/app.php) rejects a
 * state-changing request with no/invalid token before any handler runs, and
 * lets a request with a valid token through.
 *
 * Laravel bypasses this middleware whenever app('env') === 'testing'
 * (VerifyCsrfToken::runningUnitTests(), satisfied by phpunit.xml's own
 * APP_ENV=testing) - every test below overrides the bound 'env' to a
 * non-testing value first, so the real check actually runs. Without that
 * override every case below would pass regardless of the token, proving
 * nothing.
 */
class CsrfProtectionTest extends TestCase
{
    private static bool $handlerRan = false;

    protected function setUp(): void
    {
        parent::setUp();

        self::$handlerRan = false;

        Route::post('/__csrf-baseline-test', function () {
            self::$handlerRan = true;

            return response('ok');
        })->middleware('web');
    }

    public function test_post_without_token_is_rejected_before_handler_runs(): void
    {
        $this->app['env'] = 'production';

        $response = $this->post('/__csrf-baseline-test');

        $response->assertStatus(419);
        $this->assertFalse(self::$handlerRan);
    }

    public function test_post_with_invalid_token_is_rejected_before_handler_runs(): void
    {
        $this->app['env'] = 'production';

        $response = $this->withSession(['_token' => 'a-real-session-token'])
            ->post('/__csrf-baseline-test', ['_token' => 'a-different-token']);

        $response->assertStatus(419);
        $this->assertFalse(self::$handlerRan);
    }

    public function test_post_with_valid_token_reaches_handler(): void
    {
        $this->app['env'] = 'production';

        $response = $this->withSession(['_token' => 'a-real-session-token'])
            ->post('/__csrf-baseline-test', ['_token' => 'a-real-session-token']);

        $response->assertStatus(200);
        $response->assertSee('ok');
        $this->assertTrue(self::$handlerRan);
    }
}
