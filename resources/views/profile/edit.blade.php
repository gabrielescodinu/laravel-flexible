@extends('layouts.app')

@section('title', 'Modifica profilo')

@section('content')
    <section class="panel">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Account</p>
            <h1 class="mt-1 text-3xl font-bold text-slate-900">Modifica profilo</h1>
            <p class="muted mt-2">Aggiorna i tuoi dati account. La password viene cambiata solo se compili i campi dedicati.</p>
        </div>
    </section>

    <form class="panel mt-4 space-y-4" method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')

        <div class="grid gap-4 md:grid-cols-2">
            <div class="field">
                <label class="field-label" for="name">Nome</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="field-input"
                    required
                    value="{{ old('name', $user->name) }}"
                >
            </div>

            <div class="field">
                <label class="field-label" for="email">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    class="field-input"
                    required
                    value="{{ old('email', $user->email) }}"
                >
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="field">
                <label class="field-label" for="password">Nuova password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="field-input"
                    autocomplete="new-password"
                >
            </div>

            <div class="field">
                <label class="field-label" for="password_confirmation">Conferma nuova password</label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    class="field-input"
                    autocomplete="new-password"
                >
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">Salva profilo</button>
            <a href="{{ route('pages.home') }}" class="btn btn-secondary">Torna al sito</a>
        </div>
    </form>
@endsection
