<?php

namespace Tests\Feature;

use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_hides_demo_tools_outside_demo_mode(): void
    {
        config(['app.demo_mode' => false]);

        $this->get('/login')
            ->assertOk()
            ->assertSee('أهلاً بعودتك')
            ->assertDontSee('عرض تجريبي')
            ->assertDontSee('demo@dukkani.jo')
            ->assertDontSee('demo1234')
            ->assertDontSee('كلمة المرور التجريبية');
    }

    public function test_demo_login_is_not_found_outside_demo_mode(): void
    {
        config(['app.demo_mode' => false]);

        $this->post('/demo-login')->assertNotFound();
    }

    public function test_root_redirects_to_the_configured_store_outside_demo_mode(): void
    {
        $this->seed(DemoSeeder::class);

        config(['app.demo_mode' => false, 'app.default_store_slug' => 'amal-bakes']);

        $this->get('/')->assertRedirect(route('storefront.home', ['store' => 'amal-bakes']));
    }

    public function test_storefront_hides_demo_labels_outside_demo_mode(): void
    {
        $this->seed(DemoSeeder::class);

        config(['app.demo_mode' => false]);

        $this->get('/s/amal-bakes')
            ->assertOk()
            ->assertDontSee('متجر تجريبي')
            ->assertDontSee('عرض تجريبي')
            ->assertDontSee('Amal Bakes');
    }
}