@extends('layouts.app')

@section('title', 'Crea account')

@section('content')
    <section class="mx-auto w-full max-w-md panel">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Customer area</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Crea account</h1>
        <p class="muted mt-2">Registrati per completare gli acquisti e monitorare i tuoi ordini.</p>

        <form method="POST" action="{{ route('customer.register.store') }}" class="mt-6 space-y-4">
            @csrf

            <div class="field">
                <label class="field-label" for="name">Nome</label>
                <input class="field-input" id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
            </div>

            <div class="field">
                <label class="field-label" for="email">Email</label>
                <input class="field-input" id="email" name="email" type="email" value="{{ old('email') }}" required>
            </div>

            <div class="field">
                <label class="field-label" for="password">Password</label>
                <input class="field-input" id="password" name="password" type="password" minlength="8" required>
            </div>

            <div class="field">
                <label class="field-label" for="password_confirmation">Conferma password</label>
                <input class="field-input" id="password_confirmation" name="password_confirmation" type="password" minlength="8" required>
            </div>

            <button type="submit" class="btn btn-primary w-full">Registrati</button>
        </form>

        <p class="mt-4 text-sm text-slate-600">
            Hai gia un account?
            <a href="{{ route('customer.login') }}" class="font-semibold text-slate-900 underline underline-offset-2">Accedi</a>
        </p>
    </section>
@endsection

