@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    @php
        $items = $cartSnapshot['items'];
        $total = (float) $cartSnapshot['total'];
    @endphp

    <section class="panel">
        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Ecommerce</p>
        <h1 class="mt-1 text-3xl font-bold text-slate-900">Checkout</h1>
        <p class="muted mt-2">Conferma i tuoi dati e scegli il metodo di pagamento.</p>
    </section>

    <div class="mt-4 grid gap-4 lg:grid-cols-[minmax(0,1fr)_360px]">
        <form method="POST" action="{{ route('checkout.store') }}" class="panel space-y-4">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <div class="field">
                    <label class="field-label" for="customer_name">Nome e cognome</label>
                    <input
                        class="field-input"
                        id="customer_name"
                        name="customer_name"
                        type="text"
                        value="{{ old('customer_name', $user?->name) }}"
                        required
                    >
                </div>

                <div class="field">
                    <label class="field-label" for="customer_email">Email</label>
                    <input
                        class="field-input"
                        id="customer_email"
                        name="customer_email"
                        type="email"
                        value="{{ old('customer_email', $user?->email) }}"
                        required
                    >
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="payment_method">Metodo di pagamento</label>
                @php($currentPaymentMethod = old('payment_method', 'card_demo'))
                <select class="field-input" id="payment_method" name="payment_method" required>
                    <option value="card_demo" @selected($currentPaymentMethod === 'card_demo')>Carta (demo, pagamento immediato)</option>
                    <option value="bank_transfer" @selected($currentPaymentMethod === 'bank_transfer')>Bonifico (ordine in attesa)</option>
                </select>
            </div>

            <div
                class="{{ $currentPaymentMethod === 'card_demo' ? '' : 'hidden' }} space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-4"
                data-card-fields
            >
                <div>
                    <p class="text-sm font-semibold text-slate-800">Dati carta test</p>
                    <p class="mt-1 text-xs text-slate-500">
                        Carte approvate: `4242 4242 4242 4242`, `5555 5555 5555 4444`, `3782 822463 10005`.
                        Carta rifiutata: `4000 0000 0000 0002`.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="field md:col-span-2">
                        <label class="field-label" for="card_number">Numero carta</label>
                        <input
                            class="field-input"
                            id="card_number"
                            name="card_number"
                            type="text"
                            inputmode="numeric"
                            autocomplete="cc-number"
                            value="{{ old('card_number') }}"
                            placeholder="4242 4242 4242 4242"
                        >
                    </div>

                    <div class="field">
                        <label class="field-label" for="card_expiry_month">Mese scadenza</label>
                        <input
                            class="field-input"
                            id="card_expiry_month"
                            name="card_expiry_month"
                            type="number"
                            min="1"
                            max="12"
                            value="{{ old('card_expiry_month') }}"
                            placeholder="12"
                        >
                    </div>

                    <div class="field">
                        <label class="field-label" for="card_expiry_year">Anno scadenza</label>
                        <input
                            class="field-input"
                            id="card_expiry_year"
                            name="card_expiry_year"
                            type="number"
                            min="{{ now()->format('Y') }}"
                            max="{{ now()->addYears(20)->format('Y') }}"
                            value="{{ old('card_expiry_year') }}"
                            placeholder="{{ now()->addYears(2)->format('Y') }}"
                        >
                    </div>
                </div>

                <div class="field max-w-40">
                    <label class="field-label" for="card_cvc">CVC</label>
                    <input
                        class="field-input"
                        id="card_cvc"
                        name="card_cvc"
                        type="text"
                        inputmode="numeric"
                        autocomplete="cc-csc"
                        value="{{ old('card_cvc') }}"
                        placeholder="123"
                    >
                </div>
            </div>

            <div class="field">
                <label class="field-label" for="notes">Note ordine (opzionale)</label>
                <textarea class="field-input min-h-24" id="notes" name="notes" placeholder="Note per il tuo ordine...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">Conferma e paga</button>
                <a class="btn btn-secondary" href="{{ route('cart.index') }}">Torna al carrello</a>
            </div>
        </form>

        <aside class="panel">
            <h2 class="text-lg font-semibold text-slate-900">Riepilogo ordine</h2>
            <div class="mt-4 space-y-3">
                @foreach ($items as $item)
                    @php($product = $item['product'])
                    <div class="flex items-center justify-between gap-2 text-sm">
                        <div>
                            <p class="font-medium text-slate-900">{{ $product->title }}</p>
                            <p class="text-slate-500">Quantita: {{ $item['quantity'] }}</p>
                        </div>
                        <p class="font-semibold text-slate-900">€ {{ number_format((float) $item['line_total'], 2, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 border-t border-slate-200 pt-4">
                <p class="flex items-center justify-between text-lg font-semibold text-slate-900">
                    <span>Totale</span>
                    <span>€ {{ number_format($total, 2, ',', '.') }}</span>
                </p>
            </div>
        </aside>
    </div>

    <script>
        (() => {
            const paymentMethod = document.getElementById('payment_method');
            const cardFields = document.querySelector('[data-card-fields]');

            if (!paymentMethod || !cardFields) {
                return;
            }

            const syncCardFieldsVisibility = () => {
                const showCardFields = paymentMethod.value === 'card_demo';
                cardFields.classList.toggle('hidden', !showCardFields);
            };

            paymentMethod.addEventListener('change', syncCardFieldsVisibility);
            syncCardFieldsVisibility();
        })();
    </script>
@endsection
