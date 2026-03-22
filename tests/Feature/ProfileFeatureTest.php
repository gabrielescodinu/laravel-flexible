<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_profile_edit_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Modifica profilo');
    }

    public function test_user_can_update_profile_data_and_password(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Nuovo Nome',
                'email' => 'nuovo@example.com',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertSame('Nuovo Nome', $user->name);
        $this->assertSame('nuovo@example.com', $user->email);
        $this->assertTrue(Hash::check('new-password-123', $user->password));
    }
}
