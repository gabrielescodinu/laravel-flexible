<?php

use App\Support\Access\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('group_name')->nullable();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['role_id', 'user_id']);
        });

        Schema::create('permission_role', function (Blueprint $table): void {
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('permission_user', function (Blueprint $table): void {
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['permission_id', 'user_id']);
        });

        $now = now();

        $permissionRows = collect(Permissions::definitions())
            ->map(static fn (array $definition): array => [
                'name' => $definition['name'],
                'label' => $definition['label'],
                'group_name' => $definition['group'],
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        if ($permissionRows !== []) {
            DB::table('permissions')->insert($permissionRows);
        }

        $roleRows = collect(Permissions::roleTemplates())
            ->map(static fn (array $role): array => [
                'name' => $role['name'],
                'slug' => $role['slug'],
                'description' => $role['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        if ($roleRows !== []) {
            DB::table('roles')->insert($roleRows);
        }

        $permissionIdsByName = DB::table('permissions')
            ->pluck('id', 'name')
            ->all();

        $roleIdsBySlug = DB::table('roles')
            ->pluck('id', 'slug')
            ->all();

        $permissionRoleRows = [];
        foreach (Permissions::roleTemplates() as $roleTemplate) {
            $roleId = $roleIdsBySlug[$roleTemplate['slug']] ?? null;
            if (! $roleId) {
                continue;
            }

            foreach ($roleTemplate['permissions'] as $permissionName) {
                $permissionId = $permissionIdsByName[$permissionName] ?? null;
                if (! $permissionId) {
                    continue;
                }

                $permissionRoleRows[] = [
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($permissionRoleRows !== []) {
            DB::table('permission_role')->insert($permissionRoleRows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
