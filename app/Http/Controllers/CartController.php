<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SiteSetting;
use App\Support\Ecommerce\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $this->ensureEcommerceEnabled();

        $snapshot = $this->cartService->buildSnapshot($request);
        $items = $snapshot['items'];
        $total = $snapshot['total'];

        if ($this->wantsJson($request)) {
            return response()->json([
                'cart' => $this->cartService->jsonPayload($snapshot),
            ]);
        }

        return view('cart.index', [
            'items' => $items,
            'total' => $total,
            'cartSnapshot' => $this->cartService->jsonPayload($snapshot),
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
        $cartItems = $this->cartService->cartItems($request);
        $current = (int) ($cartItems[$product->id] ?? 0);

        $cartItems[$product->id] = min(99, $current + $quantity);
        $this->cartService->storeCartItems($request, $cartItems);

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => 'Prodotto aggiunto al carrello.',
                'cart' => $this->cartService->jsonPayload($this->cartService->buildSnapshot($request)),
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

        $cartItems = $this->cartService->cartItems($request);
        $cartItems[$product->id] = (int) $validated['quantity'];
        $this->cartService->storeCartItems($request, $cartItems);

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => 'Quantita aggiornata.',
                'cart' => $this->cartService->jsonPayload($this->cartService->buildSnapshot($request)),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Quantita aggiornata.');
    }

    public function remove(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $this->ensureEcommerceEnabled();

        $cartItems = $this->cartService->cartItems($request);
        unset($cartItems[$product->id]);
        $this->cartService->storeCartItems($request, $cartItems);

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => 'Prodotto rimosso dal carrello.',
                'cart' => $this->cartService->jsonPayload($this->cartService->buildSnapshot($request)),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Prodotto rimosso dal carrello.');
    }

    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $this->ensureEcommerceEnabled();

        $this->cartService->clear($request);

        if ($this->wantsJson($request)) {
            return response()->json([
                'message' => 'Carrello svuotato.',
                'cart' => $this->cartService->jsonPayload($this->cartService->buildSnapshot($request)),
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Carrello svuotato.');
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
}
