<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\SiteSetting;
use App\Support\Ecommerce\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $this->ensureEcommerceEnabled();

        $snapshot = $this->cartService->buildSnapshot($request);
        if ($snapshot['items'] === []) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Il carrello e vuoto.');
        }

        return view('checkout.index', [
            'cartSnapshot' => $snapshot,
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureEcommerceEnabled();

        $snapshot = $this->cartService->buildSnapshot($request);
        if ($snapshot['items'] === []) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Il carrello e vuoto.');
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'payment_method' => ['required', 'in:card_demo,bank_transfer'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        abort_if(! $user, 403);

        $paymentMethod = (string) $validated['payment_method'];
        $isImmediatePayment = $paymentMethod === 'card_demo';

        $orderStatus = $isImmediatePayment ? Order::STATUS_COMPLETED : Order::STATUS_PENDING;
        $paymentStatus = $isImmediatePayment ? Order::PAYMENT_PAID : Order::PAYMENT_PENDING;
        $paidAt = $isImmediatePayment ? now() : null;

        $order = DB::transaction(function () use ($snapshot, $validated, $user, $orderStatus, $paymentStatus, $paymentMethod, $paidAt, $request, $isImmediatePayment): Order {
            $order = Order::query()->create([
                'user_id' => $user->id,
                'number' => 'TMP-'.Str::uuid()->toString(),
                'status' => $orderStatus,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'currency' => 'EUR',
                'subtotal' => (float) $snapshot['total'],
                'total' => (float) $snapshot['total'],
                'customer_name' => trim((string) $validated['customer_name']),
                'customer_email' => trim((string) $validated['customer_email']),
                'notes' => filled($validated['notes'] ?? null) ? trim((string) $validated['notes']) : null,
                'paid_at' => $paidAt,
                'placed_at' => now(),
            ]);

            $order->forceFill([
                'number' => $this->generateOrderNumber($order),
            ])->save();

            foreach ($snapshot['items'] as $item) {
                $product = $item['product'];

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_title' => (string) $product->title,
                    'product_slug' => (string) $product->slug,
                    'product_image' => $product->featured_image ? (string) $product->featured_image : null,
                    'unit_price' => (float) $product->price,
                    'quantity' => (int) $item['quantity'],
                    'line_total' => (float) $item['line_total'],
                ]);
            }

            OrderPayment::query()->create([
                'order_id' => $order->id,
                'provider' => 'demo',
                'status' => $paymentStatus,
                'transaction_reference' => $isImmediatePayment
                    ? 'PAY-'.Str::upper(Str::random(12))
                    : 'BANK-'.Str::upper(Str::random(12)),
                'amount' => (float) $snapshot['total'],
                'currency' => 'EUR',
                'payload' => [
                    'payment_method' => $paymentMethod,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                'paid_at' => $paidAt,
            ]);

            return $order->fresh(['items', 'payments']);
        });

        $this->cartService->clear($request);

        return redirect()
            ->route('account.orders.show', $order)
            ->with('success', $isImmediatePayment
                ? 'Pagamento completato. Ordine confermato.'
                : 'Ordine inviato. In attesa del pagamento.');
    }

    private function ensureEcommerceEnabled(): void
    {
        if (! SiteSetting::ecommerceEnabled()) {
            abort(404);
        }
    }

    private function generateOrderNumber(Order $order): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
    }
}
