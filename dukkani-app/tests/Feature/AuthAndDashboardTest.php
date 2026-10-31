<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('أهلاً بعودتك، أمل')
            ->assertSee('عرض تجريبي');
    }

    public function test_demo_login_authenticates_the_demo_owner(): void
    {
        $this->seed(DemoSeeder::class);

        $this->post('/demo-login')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs(User::where('email', 'demo@dukkani.jo')->first());
    }

    public function test_seeded_dashboard_renders(): void
    {
        $this->seed(DemoSeeder::class);

        $user = User::where('email', 'demo@dukkani.jo')->firstOrFail();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('نظرة عامة')
            ->assertSee('أمل بيكنز')
            ->assertSee('جدول اليوم')
            ->assertSee('إيرادات الشهر');
    }

    public function test_logout_ends_the_session(): void
    {
        $this->seed(DemoSeeder::class);

        $user = User::where('email', 'demo@dukkani.jo')->firstOrFail();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
