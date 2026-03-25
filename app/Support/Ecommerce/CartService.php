<?php

namespace App\Support\Ecommerce;

use App\Models\Product;
use Illuminate\Http\Request;

class CartService
{
    /**
     * @return array<string, int>
     */
    public function cartItems(Request $request): array
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
    public function storeCartItems(Request $request, array $items): void
    {
        $normalizedItems = collect($items)
            ->filter(static fn (mixed $quantity): bool => (int) $quantity > 0)
            ->map(static fn (mixed $quantity): int => min(99, max(1, (int) $quantity)))
            ->all();

        $request->session()->put('cart.items', $normalizedItems);
    }

    public function clear(Request $request): void
    {
        $this->storeCartItems($request, []);
    }

    /**
     * @return array{items: array<int, array{product: Product, quantity: int, line_total: float}>, total: float}
     */
    public function buildSnapshot(Request $request): array
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
    public function jsonPayload(array $snapshot): array
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

    public function formatPrice(float $value): string
    {
        return '€ '.number_format($value, 2, ',', '.');
    }
}
