<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Access\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductEcommerceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_and_publish_it(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'title' => 'Smart Lamp',
                'slug' => 'smart-lamp',
                'description' => 'Lampada smart con controllo remoto.',
                'featured_image' => '/storage/media/lamp.jpg',
                'price' => '89.90',
                'status' => 'published',
            ])
            ->assertRedirect();

        $product = Product::query()->where('slug', 'smart-lamp')->firstOrFail();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Smart Lamp',
            'status' => 'published',
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Smart Lamp');

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Smart Lamp');
    }

    public function test_cart_routes_are_hidden_when_ecommerce_is_disabled(): void
    {
        $product = Product::query()->create([
            'title' => 'Desk Pro',
            'slug' => 'desk-pro',
            'description' => 'Scrivania premium',
            'featured_image' => null,
            'price' => '199.00',
            'status' => 'published',
        ]);

        $this->assertFalse(SiteSetting::ecommerceEnabled());

        $this->get(route('products.index'))
            ->assertOk()
            ->assertDontSee('Aggiungi al carrello');

        $this->post(route('cart.add', ['product' => $product->id]), [
            'quantity' => 1,
        ])->assertNotFound();
    }

    public function test_ecommerce_toggle_enables_cart_and_purchase_flow(): void
    {
        $admin = User::factory()->create();
        $product = Product::query()->create([
            'title' => 'Monitor 4K',
            'slug' => 'monitor-4k',
            'description' => 'Monitor professionale',
            'featured_image' => null,
            'price' => '399.00',
            'status' => 'published',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'ecommerce_enabled' => '1',
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertTrue(SiteSetting::ecommerceEnabled());

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Aggiungi al carrello');

        $this->post(route('cart.add', ['product' => $product->id]), [
            'quantity' => 2,
        ])->assertRedirect(route('cart.index'));

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Monitor 4K')
            ->assertSee('798,00');

        $this->patch(route('cart.update', ['product' => $product->id]), [
            'quantity' => 3,
        ])->assertRedirect(route('cart.index'));

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('1.197,00');

        $this->delete(route('cart.remove', ['product' => $product->id]))
            ->assertRedirect(route('cart.index'));

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Il carrello e vuoto');
    }

    public function test_permissions_gate_products_and_settings_sections(): void
    {
        User::factory()->create();
        $limitedUser = User::factory()->create();

        $limitedUser->permissions()->sync([
            Permission::query()->where('name', Permissions::MANAGE_ARTICLES)->firstOrFail()->id,
        ]);

        $this->actingAs($limitedUser)
            ->get(route('admin.products.index'))
            ->assertForbidden();

        $this->actingAs($limitedUser)
            ->get(route('admin.settings.edit'))
            ->assertForbidden();
    }
}
