<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_complete_checkout_with_test_card(): void
    {
        SiteSetting::setValue('ecommerce_enabled', true);

        $customer = User::factory()->create();
        $product = Product::query()->create([
            'title' => 'Keyboard Pro',
            'slug' => 'keyboard-pro',
            'description' => 'Test product',
            'featured_image' => null,
            'price' => '129.00',
            'status' => 'published',
        ]);

        $this->actingAs($customer);

        $this->post(route('cart.add', ['product' => $product->id]), [
            'quantity' => 2,
        ])->assertRedirect(route('cart.index'));

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Keyboard Pro');

        $response = $this->post(route('checkout.store'), [
            'customer_name' => 'Mario Rossi',
            'customer_email' => 'mario@example.com',
            'payment_method' => 'card_demo',
            'card_number' => '4242 4242 4242 4242',
            'card_expiry_month' => 12,
            'card_expiry_year' => now()->addYear()->format('Y'),
            'card_cvc' => '123',
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('orders', 1);

        $order = Order::query()->latest('id')->first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('account.orders.show', $order));

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $customer->id,
            'status' => Order::STATUS_COMPLETED,
            'payment_status' => Order::PAYMENT_PAID,
            'payment_method' => 'card_demo',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_title' => 'Keyboard Pro',
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('order_payments', [
            'order_id' => $order->id,
            'status' => Order::PAYMENT_PAID,
        ]);

        $this->assertSame([], session('cart.items', []));
    }

    public function test_checkout_rejects_declined_test_card_number(): void
    {
        SiteSetting::setValue('ecommerce_enabled', true);

        $customer = User::factory()->create();
        $product = Product::query()->create([
            'title' => 'Mouse Pro',
            'slug' => 'mouse-pro',
            'description' => 'Test product',
            'featured_image' => null,
            'price' => '79.00',
            'status' => 'published',
        ]);

        $this->actingAs($customer);

        $this->post(route('cart.add', ['product' => $product->id]), [
            'quantity' => 1,
        ])->assertRedirect(route('cart.index'));

        $this->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'customer_name' => 'Mario Rossi',
                'customer_email' => 'mario@example.com',
                'payment_method' => 'card_demo',
                'card_number' => '4000 0000 0000 0002',
                'card_expiry_month' => 12,
                'card_expiry_year' => now()->addYear()->format('Y'),
                'card_cvc' => '123',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('card_number');

        $this->assertDatabaseCount('orders', 0);
    }
}
