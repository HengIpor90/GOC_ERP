<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_products_page(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_open_the_dedicated_products_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('products.index'))
            ->assertOk()
            ->assertViewIs('products.index')
            ->assertSee('Add new product');
    }

    public function test_authenticated_user_can_create_update_and_delete_a_product(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('products.store'), [
            'name' => 'Premium Cement',
            'sku' => 'CEM-001',
            'category' => 'Cement',
            'price' => 8.50,
            'stock' => 120,
            'status' => 'active',
        ])->assertRedirect(route('products.index'));

        $product = Product::query()->where('sku', 'CEM-001')->firstOrFail();

        $this->actingAs($user)->patch(route('products.update', $product), [
            'name' => 'Premium Cement 50kg',
            'sku' => 'CEM-001',
            'category' => 'Cement',
            'price' => 9,
            'stock' => 100,
            'status' => 'active',
        ])->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Premium Cement 50kg',
            'stock' => 100,
        ]);

        $this->actingAs($user)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_products_page_can_search_by_sku(): void
    {
        $user = User::factory()->create();

        Product::query()->create([
            'name' => 'Red Brick',
            'sku' => 'BRICK-RED',
            'category' => 'Brick',
            'price' => 0.25,
            'stock' => 500,
            'status' => 'active',
        ]);

        Product::query()->create([
            'name' => 'White Paint',
            'sku' => 'PAINT-WHITE',
            'category' => 'Paint',
            'price' => 20,
            'stock' => 15,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('products.index', ['q' => 'BRICK-RED']))
            ->assertOk()
            ->assertSee('Red Brick')
            ->assertDontSee('White Paint');
    }
}
