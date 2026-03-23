<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->with(['roles:id,name,slug', 'permissions:id,name,label'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(),
            'roles' => Role::query()->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('group_name')->orderBy('label')->get(),
            'selectedRoleIds' => [],
            'selectedPermissionIds' => [],
            'formAction' => route('admin.users.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Crea utente',
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $user = DB::transaction(function () use ($validated): User {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $roleIds = collect($validated['role_ids'] ?? [])
                ->map(static fn (mixed $id): int => (int) $id)
                ->unique()
                ->values()
                ->all();

            $permissionIds = collect($validated['permission_ids'] ?? [])
                ->map(static fn (mixed $id): int => (int) $id)
                ->unique()
                ->values()
                ->all();

            $user->roles()->sync($roleIds);
            $user->permissions()->sync($permissionIds);

            return $user->refresh();
        });

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'Utente creato con successo.');
    }

    public function edit(User $user): View
    {
        $user->load(['roles:id', 'permissions:id']);

        return view('admin.users.form', [
            'user' => $user,
            'roles' => Role::query()->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('group_name')->orderBy('label')->get(),
            'selectedRoleIds' => $user->roles->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all(),
            'selectedPermissionIds' => $user->permissions->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all(),
            'formAction' => route('admin.users.update', $user),
            'formMethod' => 'PUT',
            'submitLabel' => 'Salva utente',
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validatePayload($request, $user);

        DB::transaction(function () use ($validated, $user): void {
            $user->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if (filled($validated['password'] ?? null)) {
                $user->password = Hash::make((string) $validated['password']);
            }

            $user->save();

            $roleIds = collect($validated['role_ids'] ?? [])
                ->map(static fn (mixed $id): int => (int) $id)
                ->unique()
                ->values()
                ->all();

            $permissionIds = collect($validated['permission_ids'] ?? [])
                ->map(static fn (mixed $id): int => (int) $id)
                ->unique()
                ->values()
                ->all();

            $user->roles()->sync($roleIds);
            $user->permissions()->sync($permissionIds);
        });

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'Utente aggiornato.');
    }

    public function destroy(User $user, Request $request): RedirectResponse
    {
        if ((int) $request->user()?->id === (int) $user->id) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors(['name' => 'Non puoi eliminare il tuo stesso account.']);
        }

        DB::transaction(function () use ($user): void {
            $user->roles()->detach();
            $user->permissions()->detach();
            $user->delete();
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utente eliminato.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePayload(Request $request, ?User $user = null): array
    {
        $isEdit = $user?->exists ?? false;

        $passwordRules = $isEdit
            ? ['nullable', 'string', 'min:8', 'confirmed']
            : ['required', 'string', 'min:8', 'confirmed'];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => $passwordRules,
            'role_ids' => ['array'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);
    }
}
