@extends('layouts.app')

@section('title', 'Nessuna pagina pubblicata')

@section('content')
    <section class="panel text-center">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Setup iniziale</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Nessuna pagina pubblicata</h1>
        <p class="muted mx-auto mt-3 max-w-xl">
            Crea una pagina in admin e imposta lo stato su <code class="rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-700">published</code> per vederla qui.
        </p>
        <a class="btn btn-primary mt-6" href="{{ route('admin.pages.create') }}">Crea la prima pagina</a>
    </section>
@endsection
