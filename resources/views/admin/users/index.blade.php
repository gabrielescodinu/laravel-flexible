@extends('layouts.admin')

@section('title', 'Utenze')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Access management</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Utenti, ruoli e permessi</h1>
                <p class="muted mt-2">Crea utenze e assegna ruoli/permessi granulari (pagine, articoli, categorie, media, menu).</p>
            </div>
            <a class="btn btn-primary" href="{{ route('admin.users.create') }}">Nuovo utente</a>
        </div>
    </section>

    <section class="panel mt-4 space-y-3">
        @if ($users->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                <p class="text-sm text-slate-500">Nessuna utenza disponibile.</p>
            </div>
        @else
            @foreach ($users as $managedUser)
                <article class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $managedUser->name }}</p>
                            <p class="text-xs text-slate-500">{{ $managedUser->email }}</p>

                            @if ($managedUser->roles->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach ($managedUser->roles as $role)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if ($managedUser->permissions->isNotEmpty())
                                <p class="mt-2 text-xs text-slate-500">Permessi diretti: {{ $managedUser->permissions->count() }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a class="btn btn-secondary" href="{{ route('admin.users.edit', $managedUser) }}">Modifica</a>

                            @if ((int) auth()->id() !== (int) $managedUser->id)
                                <form
                                    action="{{ route('admin.users.destroy', $managedUser) }}"
                                    method="POST"
                                    data-confirm-title="Elimina utente"
                                    data-confirm-message="Vuoi eliminare questo utente? L'azione non puo essere annullata."
                                    data-confirm-label="Elimina"
                                    data-confirm-variant="danger"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger" type="submit">Elimina</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach

            <div class="pt-2">
                {{ $users->links() }}
            </div>
        @endif
    </section>
@endsection
