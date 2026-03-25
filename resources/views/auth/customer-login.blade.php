@extends('layouts.app')

@section('title', 'Accedi al tuo account')

@section('content')
    <section class="mx-auto w-full max-w-md panel">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Customer area</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Accedi</h1>
        <p class="muted mt-2">Visualizza ordini in corso e acquisti completati.</p>

        <form method="POST" action="{{ route('customer.login.store') }}" class="mt-6 space-y-4">
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

            <button type="submit" class="btn btn-primary w-full">Accedi</button>
        </form>

        <p class="mt-4 text-sm text-slate-600">
            Non hai un account?
            <a href="{{ route('customer.register') }}" class="font-semibold text-slate-900 underline underline-offset-2">Registrati</a>
        </p>
    </section>
@endsection

