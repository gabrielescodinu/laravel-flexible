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
use Illuminate\Validation\ValidationException;

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
            'card_number' => ['nullable', 'string', 'required_if:payment_method,card_demo'],
            'card_expiry_month' => ['nullable', 'integer', 'required_if:payment_method,card_demo', 'min:1', 'max:12'],
            'card_expiry_year' => ['nullable', 'integer', 'required_if:payment_method,card_demo', 'min:2024', 'max:2100'],
            'card_cvc' => ['nullable', 'string', 'required_if:payment_method,card_demo', 'min:3', 'max:4'],
        ]);

        $user = $request->user();
        abort_if(! $user, 403);

        $paymentMethod = (string) $validated['payment_method'];
        $isImmediatePayment = $paymentMethod === 'card_demo';
        $cardDetails = null;

        if ($isImmediatePayment) {
            $cardDetails = $this->validateDemoCard($validated);
        }

        $orderStatus = $isImmediatePayment ? Order::STATUS_COMPLETED : Order::STATUS_PENDING;
        $paymentStatus = $isImmediatePayment ? Order::PAYMENT_PAID : Order::PAYMENT_PENDING;
        $paidAt = $isImmediatePayment ? now() : null;

        $order = DB::transaction(function () use ($snapshot, $validated, $user, $orderStatus, $paymentStatus, $paymentMethod, $paidAt, $request, $isImmediatePayment, $cardDetails): Order {
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
                    'card' => $cardDetails,
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

    /**
     * @param  array<string, mixed>  $validated
     * @return array{brand: string, last4: string, exp_month: int, exp_year: int}
     * @throws ValidationException
     */
    private function validateDemoCard(array $validated): array
    {
        $cardNumber = preg_replace('/\D+/', '', (string) ($validated['card_number'] ?? ''));
        $expMonth = (int) ($validated['card_expiry_month'] ?? 0);
        $expYear = (int) ($validated['card_expiry_year'] ?? 0);
        $cvc = preg_replace('/\D+/', '', (string) ($validated['card_cvc'] ?? ''));

        if ($cardNumber === '' || strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            throw ValidationException::withMessages([
                'card_number' => 'Numero carta non valido.',
            ]);
        }

        if ($cvc === '' || strlen($cvc) < 3 || strlen($cvc) > 4) {
            throw ValidationException::withMessages([
                'card_cvc' => 'CVC non valido.',
            ]);
        }

        $currentYear = (int) now()->format('Y');
        $currentMonth = (int) now()->format('n');
        if ($expYear < $currentYear || ($expYear === $currentYear && $expMonth < $currentMonth)) {
            throw ValidationException::withMessages([
                'card_expiry_year' => 'Carta scaduta.',
            ]);
        }

        $acceptedTestCards = [
            '4242424242424242', // Visa test success
            '5555555555554444', // Mastercard test success
            '378282246310005',  // Amex test success
        ];

        $declinedTestCards = [
            '4000000000000002', // Generic decline
            '4000000000009995', // Insufficient funds
        ];

        if (in_array($cardNumber, $declinedTestCards, true)) {
            throw ValidationException::withMessages([
                'card_number' => 'Pagamento rifiutato (carta test simulata).',
            ]);
        }

        if (! in_array($cardNumber, $acceptedTestCards, true)) {
            throw ValidationException::withMessages([
                'card_number' => 'Carta test non riconosciuta. Usa uno dei numeri test suggeriti.',
            ]);
        }

        return [
            'brand' => $this->detectCardBrand($cardNumber),
            'last4' => substr($cardNumber, -4),
            'exp_month' => $expMonth,
            'exp_year' => $expYear,
        ];
    }

    private function detectCardBrand(string $cardNumber): string
    {
        if (str_starts_with($cardNumber, '4')) {
            return 'visa';
        }

        if (preg_match('/^(5[1-5])/', $cardNumber) === 1) {
            return 'mastercard';
        }

        if (preg_match('/^(34|37)/', $cardNumber) === 1) {
            return 'amex';
        }

        return 'card';
    }
}
