@extends('layouts.app')

@section('title', 'I miei ordini')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Customer area</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">I miei ordini</h1>
                <p class="muted mt-2">Storico acquisti e ordini attualmente in lavorazione.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a class="btn btn-secondary" href="{{ route('products.index') }}">Vai ai prodotti</a>
                <form method="POST" action="{{ route('customer.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Logout</button>
                </form>
            </div>
        </div>
    </section>

    <section class="panel mt-4">
        <h2 class="text-xl font-semibold text-slate-900">Ordini in corso</h2>

        @if ($inProgressOrders->isEmpty())
            <p class="muted mt-3 text-sm">Nessun ordine in corso.</p>
        @else
            <div class="mt-4 space-y-3">
                @foreach ($inProgressOrders as $order)
                    <article class="rounded-xl border border-slate-200 bg-white p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $order->number }}</p>
                                <p class="text-sm text-slate-500">
                                    {{ $order->created_at?->format('d/m/Y H:i') }} ·
                                    {{ strtoupper((string) $order->status) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-slate-900">€ {{ number_format((float) $order->total, 2, ',', '.') }}</p>
                                <a class="btn btn-secondary" href="{{ route('account.orders.show', $order) }}">Dettagli</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="panel mt-4">
        <h2 class="text-xl font-semibold text-slate-900">Acquisti completati</h2>

        @if ($completedOrders->isEmpty())
            <p class="muted mt-3 text-sm">Non ci sono acquisti completati.</p>
        @else
            <div class="mt-4 space-y-3">
                @foreach ($completedOrders as $order)
                    <article class="rounded-xl border border-slate-200 bg-white p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $order->number }}</p>
                                <p class="text-sm text-slate-500">
                                    {{ $order->created_at?->format('d/m/Y H:i') }} ·
                                    PAGATO
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-slate-900">€ {{ number_format((float) $order->total, 2, ',', '.') }}</p>
                                <a class="btn btn-secondary" href="{{ route('account.orders.show', $order) }}">Dettagli</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection

