<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_sends_guests_to_the_login_screen_when_no_store_exists(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }
}
