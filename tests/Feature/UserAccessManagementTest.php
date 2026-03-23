<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Access\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccessManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_and_assign_roles_and_permissions(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Utenti, ruoli e permessi');

        $editorRole = Role::query()->where('slug', 'editor')->firstOrFail();
        $mediaPermission = Permission::query()->where('name', Permissions::MANAGE_MEDIA)->firstOrFail();

        $this->post(route('admin.users.store'), [
            'name' => 'Editor Demo',
            'email' => 'editor-demo@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
            'role_ids' => [$editorRole->id],
            'permission_ids' => [$mediaPermission->id],
        ])->assertRedirect();

        $createdUser = User::query()->where('email', 'editor-demo@example.com')->firstOrFail();

        $this->assertDatabaseHas('role_user', [
            'user_id' => $createdUser->id,
            'role_id' => $editorRole->id,
        ]);

        $this->assertDatabaseHas('permission_user', [
            'user_id' => $createdUser->id,
            'permission_id' => $mediaPermission->id,
        ]);
    }

    public function test_user_permissions_are_enforced_per_admin_section(): void
    {
        $admin = User::factory()->create();
        $limitedUser = User::factory()->create();

        $manageArticlesPermission = Permission::query()->where('name', Permissions::MANAGE_ARTICLES)->firstOrFail();
        $limitedUser->permissions()->sync([$manageArticlesPermission->id]);

        $this->actingAs($limitedUser)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.articles.index'));

        $this->actingAs($limitedUser)
            ->get(route('admin.articles.index'))
            ->assertOk();

        $this->actingAs($limitedUser)
            ->get(route('admin.pages.index'))
            ->assertForbidden();

        $this->actingAs($limitedUser)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk();
    }
}
