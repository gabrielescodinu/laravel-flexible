@extends('layouts.admin')

@section('title', $isEdit ? 'Modifica utente' : 'Nuovo utente')

@section('content')
    @php
        $resolvedRoleIds = collect(old('role_ids', $selectedRoleIds ?? []))
            ->map(static fn ($id) => (int) $id)
            ->all();

        $resolvedPermissionIds = collect(old('permission_ids', $selectedPermissionIds ?? []))
            ->map(static fn ($id) => (int) $id)
            ->all();

        $groupedPermissions = $permissions->groupBy(fn ($permission) => $permission->group_name ?: 'Altro');
    @endphp

    <section class="panel">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Access management</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">{{ $isEdit ? 'Modifica utente' : 'Nuova utenza' }}</h1>
                <p class="muted mt-2">Assegna uno o piu ruoli e aggiungi eventuali permessi extra granulari.</p>
            </div>
            <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">Torna alla lista</a>
        </div>
    </section>

    <form method="POST" action="{{ $formAction }}" class="mt-4 space-y-4">
        @csrf
        @if ($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <section class="panel space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="field">
                    <label class="field-label" for="name">Nome</label>
                    <input class="field-input" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="field">
                    <label class="field-label" for="email">Email</label>
                    <input class="field-input" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="field">
                    <label class="field-label" for="password">{{ $isEdit ? 'Nuova password (opzionale)' : 'Password' }}</label>
                    <input class="field-input" id="password" name="password" type="password" {{ $isEdit ? '' : 'required' }}>
                </div>
                <div class="field">
                    <label class="field-label" for="password_confirmation">Conferma password</label>
                    <input class="field-input" id="password_confirmation" name="password_confirmation" type="password" {{ $isEdit ? '' : 'required' }}>
                </div>
            </div>
        </section>

        <section class="panel space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Ruoli</h2>
                <p class="muted mt-1 text-sm">I ruoli assegnano pacchetti di permessi predefiniti.</p>
            </div>

            @if ($roles->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                    Nessun ruolo disponibile.
                </div>
            @else
                <div class="grid gap-2 md:grid-cols-2">
                    @foreach ($roles as $role)
                        <label class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm">
                            <span class="flex items-start gap-2">
                                <input
                                    class="mt-0.5 h-4 w-4 rounded border-slate-300"
                                    type="checkbox"
                                    name="role_ids[]"
                                    value="{{ $role->id }}"
                                    @checked(in_array((int) $role->id, $resolvedRoleIds, true))
                                >
                                <span>
                                    <span class="font-medium text-slate-800">{{ $role->name }}</span>
                                    @if ($role->description)
                                        <span class="mt-0.5 block text-xs text-slate-500">{{ $role->description }}</span>
                                    @endif
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="panel space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Permessi diretti</h2>
                <p class="muted mt-1 text-sm">Permessi aggiuntivi oltre ai ruoli assegnati (override granulare).</p>
            </div>

            @if ($permissions->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                    Nessun permesso disponibile.
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($groupedPermissions as $groupName => $permissionGroup)
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">{{ $groupName }}</p>
                            <div class="mt-2 grid gap-2 md:grid-cols-2">
                                @foreach ($permissionGroup as $permission)
                                    <label class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm">
                                        <span class="flex items-center gap-2">
                                            <input
                                                class="h-4 w-4 rounded border-slate-300"
                                                type="checkbox"
                                                name="permission_ids[]"
                                                value="{{ $permission->id }}"
                                                @checked(in_array((int) $permission->id, $resolvedPermissionIds, true))
                                            >
                                            <span class="text-slate-800">{{ $permission->label }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <div class="flex flex-wrap gap-2">
            <button class="btn btn-primary" type="submit">{{ $submitLabel }}</button>
            <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">Annulla</a>
        </div>
    </form>
@endsection
