<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_when_accessing_admin_routes(): void
    {
        $this->get(route('admin.pages.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.articles.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.media.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.menus.edit'))
            ->assertRedirect(route('login'));

        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
            'remember' => '1',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
