<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('admin.products.index', [
            'products' => $products,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product([
                'status' => 'draft',
                'price' => '0.00',
            ]),
            'formAction' => route('admin.products.store'),
            'formMethod' => 'POST',
            'submitLabel' => 'Crea prodotto',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $product = $this->persist($request, new Product());

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Prodotto creato.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'formAction' => route('admin.products.update', $product),
            'formMethod' => 'PUT',
            'submitLabel' => 'Salva prodotto',
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->persist($request, $product);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Prodotto aggiornato.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Prodotto eliminato.');
    }

    private function persist(Request $request, Product $product): Product
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('products', 'slug')->ignore($product->id),
            ],
            'description' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);

        $product->fill([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'featured_image' => $validated['featured_image'] ?? null,
            'price' => number_format((float) $validated['price'], 2, '.', ''),
            'status' => $validated['status'],
        ]);
        $product->save();

        return $product->refresh();
    }
}
