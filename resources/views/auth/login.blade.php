@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <section class="mx-auto w-full max-w-md panel">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Admin Access</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Accedi al pannello</h1>
        <p class="muted mt-2">Inserisci le credenziali del tuo account amministratore.</p>

        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
            @csrf

            <div class="field">
                <label class="field-label" for="email">Email</label>
                <input class="field-input" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="field">
                <label class="field-label" for="password">Password</label>
                <input class="field-input" id="password" name="password" type="password" required>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input class="h-4 w-4 rounded border-slate-300" type="checkbox" name="remember" value="1">
                Ricordami
            </label>

            <button type="submit" class="btn btn-primary w-full">Login</button>
        </form>
    </section>
@endsection
