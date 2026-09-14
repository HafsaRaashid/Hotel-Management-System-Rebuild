<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * test-backend pillar (feature-level): proves the HTTP kernel, routing and
 * health-check pillar wiring runs end to end. Not a capability test.
 */
class HealthCheckTest extends TestCase
{
    public function test_the_health_check_endpoint_answers(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }
}
