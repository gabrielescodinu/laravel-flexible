@extends('layouts.admin')

@section('title', 'Prodotti')

@section('content')
    <section class="panel">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-400">Ecommerce</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900">Prodotti</h1>
                <p class="muted mt-2">Gestisci catalogo prodotti, prezzi e disponibilita.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if (auth()->user()?->hasPermission(\App\Support\Access\Permissions::MANAGE_SETTINGS))
                    <a class="btn btn-secondary" href="{{ route('admin.settings.edit') }}">Impostazioni ecommerce</a>
                @endif
                <a class="btn btn-primary" href="{{ route('admin.products.create') }}">Nuovo prodotto</a>
            </div>
        </div>
    </section>

    <section class="panel mt-4">
        @if ($products->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                <p class="text-sm text-slate-500">Nessun prodotto disponibile.</p>
                <a class="btn btn-primary mt-4" href="{{ route('admin.products.create') }}">Crea il primo prodotto</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="pb-3 pr-4">Prodotto</th>
                            <th class="pb-3 pr-4">Slug</th>
                            <th class="pb-3 pr-4">Prezzo</th>
                            <th class="pb-3 pr-4">Stato</th>
                            <th class="pb-3">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($products as $product)
                            <tr>
                                <td class="py-4 pr-4">
                                    <p class="font-medium text-slate-900">{{ $product->title }}</p>
                                </td>
                                <td class="py-4 pr-4">
                                    <code class="rounded-md bg-slate-100 px-2 py-1 text-xs text-slate-700">{{ $product->slug }}</code>
                                </td>
                                <td class="py-4 pr-4 font-medium text-slate-800">
                                    € {{ number_format((float) $product->price, 2, ',', '.') }}
                                </td>
                                <td class="py-4 pr-4">
                                    <span class="status-badge {{ $product->status === 'published' ? 'status-published' : 'status-draft' }}">
                                        {{ $product->status }}
                                    </span>
                                </td>
                                <td class="py-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a class="btn btn-secondary" href="{{ route('admin.products.edit', $product) }}">Modifica</a>
                                        @if ($product->status === 'published')
                                            <a class="btn btn-secondary" href="{{ $product->public_url }}" target="_blank" rel="noopener">Apri</a>
                                        @endif
                                        <form
                                            action="{{ route('admin.products.destroy', $product) }}"
                                            method="POST"
                                            data-confirm-title="Elimina prodotto"
                                            data-confirm-message="Vuoi eliminare questo prodotto?"
                                            data-confirm-label="Elimina"
                                            data-confirm-variant="danger"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger" type="submit">Elimina</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $products->links() }}
            </div>
        @endif
    </section>
@endsection
