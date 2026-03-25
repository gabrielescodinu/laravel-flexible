@extends('layouts.app')

@section('title', 'Dettaglio ordine')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Ordine</p>
                <h1 class="mt-1 text-3xl font-bold text-slate-900">{{ $order->number }}</h1>
                <p class="muted mt-2">
                    Stato: <span class="font-semibold text-slate-700">{{ strtoupper((string) $order->status) }}</span> ·
                    Pagamento: <span class="font-semibold text-slate-700">{{ strtoupper((string) $order->payment_status) }}</span>
                </p>
            </div>
            <a class="btn btn-secondary" href="{{ route('account.orders.index') }}">Torna ai miei ordini</a>
        </div>
    </section>

    <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_360px]">
        <section class="panel">
            <h2 class="text-xl font-semibold text-slate-900">Prodotti</h2>
            <div class="mt-4 space-y-3">
                @foreach ($order->items as $item)
                    <article class="rounded-xl border border-slate-200 bg-white p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $item->product_title }}</p>
                                <p class="text-sm text-slate-500">Quantita: {{ $item->quantity }}</p>
                            </div>
                            <p class="font-semibold text-slate-900">€ {{ number_format((float) $item->line_total, 2, ',', '.') }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <aside class="panel space-y-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Riepilogo</h2>
                <p class="muted mt-1 text-sm">Dati ordine e pagamento.</p>
            </div>

            <dl class="space-y-2 text-sm">
                <div class="flex items-center justify-between gap-2">
                    <dt class="text-slate-500">Data ordine</dt>
                    <dd class="font-medium text-slate-900">{{ $order->created_at?->format('d/m/Y H:i') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <dt class="text-slate-500">Cliente</dt>
                    <dd class="font-medium text-slate-900">{{ $order->customer_name }}</dd>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <dt class="text-slate-500">Email</dt>
                    <dd class="font-medium text-slate-900">{{ $order->customer_email }}</dd>
                </div>
                <div class="flex items-center justify-between gap-2">
                    <dt class="text-slate-500">Metodo</dt>
                    <dd class="font-medium text-slate-900">{{ $order->payment_method === 'bank_transfer' ? 'Bonifico' : 'Carta demo' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-2 border-t border-slate-200 pt-3">
                    <dt class="text-slate-600">Totale</dt>
                    <dd class="text-base font-semibold text-slate-900">€ {{ number_format((float) $order->total, 2, ',', '.') }}</dd>
                </div>
            </dl>

            @if (filled($order->notes))
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Note</p>
                    <p class="mt-2 text-sm text-slate-700">{{ $order->notes }}</p>
                </div>
            @endif

            @if ($order->payments->isNotEmpty())
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Transazione</p>
                    @php($payment = $order->payments->first())
                    <p class="mt-2 text-sm text-slate-700">{{ $payment?->transaction_reference }}</p>
                </div>
            @endif
        </aside>
    </div>
@endsection

