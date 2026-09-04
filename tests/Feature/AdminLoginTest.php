<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_ok(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_admin_can_sign_in(): void
    {
        $this->seed();

        $this->post('/admin/login', [
            'email' => 'admin@qiblafinder.test',
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs(User::query()->where('email', 'admin@qiblafinder.test')->first());

        $this->get('/admin')->assertOk();
    }
}
