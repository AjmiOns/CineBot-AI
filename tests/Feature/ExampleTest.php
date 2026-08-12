<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * '/' redirige vers /login (invité) ou /chatbot (connecté) — voir routes/web.php.
     */
    public function test_the_home_route_redirects_a_guest_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
