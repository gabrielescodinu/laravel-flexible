<?php

use App\Support\Access\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
            return;
        }

        $now = now();

        DB::table('permissions')->insertOrIgnore([
            [
                'name' => Permissions::MANAGE_PRODUCTS,
                'label' => 'Gestione prodotti ecommerce',
                'group_name' => 'Shop',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => Permissions::MANAGE_SETTINGS,
                'label' => 'Gestione impostazioni sito',
                'group_name' => 'Amministrazione',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        if (! Schema::hasTable('permission_role')) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', [Permissions::MANAGE_PRODUCTS, Permissions::MANAGE_SETTINGS])
            ->pluck('id', 'name')
            ->all();

        $roleIds = DB::table('roles')
            ->whereIn('slug', ['administrator', 'editor'])
            ->pluck('id', 'slug')
            ->all();

        $rows = [];

        $administratorRoleId = $roleIds['administrator'] ?? null;
        if ($administratorRoleId) {
            foreach ($permissionIds as $permissionId) {
                $rows[] = [
                    'permission_id' => $permissionId,
                    'role_id' => $administratorRoleId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $editorRoleId = $roleIds['editor'] ?? null;
        $manageProductsId = $permissionIds[Permissions::MANAGE_PRODUCTS] ?? null;
        if ($editorRoleId && $manageProductsId) {
            $rows[] = [
                'permission_id' => $manageProductsId,
                'role_id' => $editorRoleId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('permission_role')->insertOrIgnore($rows);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', [Permissions::MANAGE_PRODUCTS, Permissions::MANAGE_SETTINGS])
            ->pluck('id')
            ->all();

        if ($permissionIds === []) {
            return;
        }

        if (Schema::hasTable('permission_role')) {
            DB::table('permission_role')
                ->whereIn('permission_id', $permissionIds)
                ->delete();
        }

        if (Schema::hasTable('permission_user')) {
            DB::table('permission_user')
                ->whereIn('permission_id', $permissionIds)
                ->delete();
        }

        DB::table('permissions')
            ->whereIn('id', $permissionIds)
            ->delete();
    }
};
