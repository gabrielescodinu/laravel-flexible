<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $this->ensureEcommerceEnabled();

        $snapshot = $this->buildCartSnapshot($request);
        $items = $snapshot['items'];
        $total = $snapshot['total'];

        if ($this->wantsJson($request)) {
            return response()->json([
                'cart' => $this->cartJsonPayload($snapshot),
            ]);
        }

        return view('cart.index', [
            'items' => $items,
            'total' => $total,
            'cartSnapshot' => $this->cartJsonPayload($snapshot),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse|JsonResponse
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

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => 'Prodotto aggiunto al carrello.',
                'cart' => $this->cartJsonPayload($this->buildCartSnapshot($request)),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Prodotto aggiunto al carrello.');
    }

    public function update(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $this->ensureEcommerceEnabled();
        $this->ensurePurchasable($product);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $cartItems = $this->cartItems($request);
        $cartItems[$product->id] = (int) $validated['quantity'];
        $this->storeCartItems($request, $cartItems);

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => 'Quantita aggiornata.',
                'cart' => $this->cartJsonPayload($this->buildCartSnapshot($request)),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Quantita aggiornata.');
    }

    public function remove(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $this->ensureEcommerceEnabled();

        $cartItems = $this->cartItems($request);
        unset($cartItems[$product->id]);
        $this->storeCartItems($request, $cartItems);

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => 'Prodotto rimosso dal carrello.',
                'cart' => $this->cartJsonPayload($this->buildCartSnapshot($request)),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Prodotto rimosso dal carrello.');
    }

    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $this->ensureEcommerceEnabled();

        $this->storeCartItems($request, []);

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => 'Carrello svuotato.',
                'cart' => $this->cartJsonPayload($this->buildCartSnapshot($request)),
            ]);
        }

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

    private function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->wantsJson() || $request->ajax();
    }

    /**
     * @return array{items: array<int, array{product: Product, quantity: int, line_total: float}>, total: float}
     */
    private function buildCartSnapshot(Request $request): array
    {
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

        return [
            'items' => $items,
            'total' => $total,
        ];
    }

    /**
     * @param  array{items: array<int, array{product: Product, quantity: int, line_total: float}>, total: float}  $snapshot
     * @return array{
     *     items: array<int, array{
     *         product_id: int,
     *         title: string,
     *         url: string,
     *         featured_image: ?string,
     *         price: float,
     *         price_formatted: string,
     *         quantity: int,
     *         line_total: float,
     *         line_total_formatted: string
     *     }>,
     *     total: float,
     *     total_formatted: string,
     *     cart_count: int
     * }
     */
    private function cartJsonPayload(array $snapshot): array
    {
        $items = collect($snapshot['items'])
            ->map(function (array $item): array {
                $product = $item['product'];
                $price = (float) $product->price;
                $lineTotal = (float) $item['line_total'];

                return [
                    'product_id' => (int) $product->id,
                    'title' => (string) $product->title,
                    'url' => (string) $product->public_url,
                    'featured_image' => $product->featured_image ? (string) $product->featured_image : null,
                    'price' => $price,
                    'price_formatted' => $this->formatPrice($price),
                    'quantity' => (int) $item['quantity'],
                    'line_total' => $lineTotal,
                    'line_total_formatted' => $this->formatPrice($lineTotal),
                ];
            })
            ->values()
            ->all();

        $cartCount = (int) collect($items)->sum(static fn (array $item): int => (int) $item['quantity']);
        $total = (float) $snapshot['total'];

        return [
            'items' => $items,
            'total' => $total,
            'total_formatted' => $this->formatPrice($total),
            'cart_count' => $cartCount,
        ];
    }

    private function formatPrice(float $value): string
    {
        return '€ '.number_format($value, 2, ',', '.');
    }
}
