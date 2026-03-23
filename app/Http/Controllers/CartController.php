<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureEcommerceEnabled();

        $cartItems = $this->cartItems($request);
        $productIds = array_map('intval', array_keys($cartItems));

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('status', 'published')
            ->get()
            ->keyBy('id');

        $items = [];
        $total = 0.0;

        foreach ($cartItems as $productId => $quantity) {
            $product = $products->get((int) $productId);
            if (! $product) {
                continue;
            }

            $qty = max(1, (int) $quantity);
            $lineTotal = (float) $product->price * $qty;

            $items[] = [
                'product' => $product,
                'quantity' => $qty,
                'line_total' => $lineTotal,
            ];

            $total += $lineTotal;
        }

        return view('cart.index', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $this->ensureEcommerceEnabled();
        $this->ensurePurchasable($product);

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $cartItems = $this->cartItems($request);
        $current = (int) ($cartItems[$product->id] ?? 0);

        $cartItems[$product->id] = min(99, $current + $quantity);
        $this->storeCartItems($request, $cartItems);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Prodotto aggiunto al carrello.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->ensureEcommerceEnabled();
        $this->ensurePurchasable($product);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $cartItems = $this->cartItems($request);
        $cartItems[$product->id] = (int) $validated['quantity'];
        $this->storeCartItems($request, $cartItems);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Quantita aggiornata.');
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $this->ensureEcommerceEnabled();

        $cartItems = $this->cartItems($request);
        unset($cartItems[$product->id]);
        $this->storeCartItems($request, $cartItems);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Prodotto rimosso dal carrello.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $this->ensureEcommerceEnabled();

        $this->storeCartItems($request, []);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Carrello svuotato.');
    }

    /**
     * @return array<string, int>
     */
    private function cartItems(Request $request): array
    {
        $items = $request->session()->get('cart.items', []);
        if (! is_array($items)) {
            return [];
        }

        return collect($items)
            ->mapWithKeys(static fn (mixed $quantity, mixed $productId): array => [(string) $productId => max(1, (int) $quantity)])
            ->all();
    }

    /**
     * @param  array<string, int>  $items
     */
    private function storeCartItems(Request $request, array $items): void
    {
        $normalizedItems = collect($items)
            ->filter(static fn (mixed $quantity): bool => (int) $quantity > 0)
            ->map(static fn (mixed $quantity): int => min(99, max(1, (int) $quantity)))
            ->all();

        $request->session()->put('cart.items', $normalizedItems);
    }

    private function ensureEcommerceEnabled(): void
    {
        if (! SiteSetting::ecommerceEnabled()) {
            abort(404);
        }
    }

    private function ensurePurchasable(Product $product): void
    {
        if ($product->status !== 'published') {
            abort(404);
        }
    }
}
