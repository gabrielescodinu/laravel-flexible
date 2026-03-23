@extends('layouts.admin')

@section('title', 'Impostazioni sito')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Configurazione</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Impostazioni sito</h1>
                <p class="muted mt-2">Attiva o disattiva funzionalita globali del frontend.</p>
            </div>
            <a class="btn btn-secondary" href="{{ route('admin.products.index') }}">Vai ai prodotti</a>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-4">
        @csrf
        @method('PUT')

        <section class="panel space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Ecommerce</h2>
                <p class="muted mt-1">Quando attivo, il carrello e il flusso di acquisto diventano visibili sul sito pubblico.</p>
            </div>

            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3">
                <input
                    type="hidden"
                    name="ecommerce_enabled"
                    value="0"
                >
                <input
                    class="h-5 w-5 rounded border-slate-300"
                    type="checkbox"
                    name="ecommerce_enabled"
                    value="1"
                    @checked(old('ecommerce_enabled', $ecommerceEnabled) ? true : false)
                >
                <span>
                    <span class="block font-medium text-slate-800">Abilita ecommerce</span>
                    <span class="block text-sm text-slate-500">Mostra carrello e pulsanti di acquisto sui prodotti.</span>
                </span>
            </label>

            <div class="flex items-center gap-2">
                <button class="btn btn-primary" type="submit">Salva impostazioni</button>
            </div>
        </section>
    </form>
@endsection
