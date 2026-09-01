<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_every_sidebar_page(): void
    {
        $user = User::factory()->create();

        $pages = [
            'dashboard' => 'dashboard.index',
            'products.index' => 'products.index',
            'inventory.index' => 'inventory.index',
            'sales.index' => 'sales.index',
            'orders.index' => 'orders.index',
            'customers.index' => 'customers.index',
            'reports.index' => 'reports.index',
        ];

        foreach ($pages as $route => $view) {
            $this->actingAs($user)
                ->get(route($route))
                ->assertOk()
                ->assertViewIs($view);
        }
    }

    public function test_inventory_receive_and_issue_updates_product_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->product(stock: 10);

        $this->actingAs($user)->post(route('inventory.store'), [
            'product_id' => $product->id,
            'type' => 'receive',
            'quantity' => 5,
            'note' => 'Supplier delivery',
        ])->assertRedirect(route('inventory.index'));

        $this->assertSame(15, $product->fresh()->stock);

        $this->actingAs($user)->post(route('inventory.store'), [
            'product_id' => $product->id,
            'type' => 'issue',
            'quantity' => 4,
            'note' => 'Warehouse issue',
        ])->assertRedirect(route('inventory.index'));

        $this->assertSame(11, $product->fresh()->stock);
        $this->assertSame(2, StockMovement::query()->count());
    }

    public function test_sale_decreases_stock_and_deleting_sale_restores_it(): void
    {
        $user = User::factory()->create();
        $product = $this->product(stock: 20);

        $this->actingAs($user)->post(route('sales.store'), [
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 12.50,
        ])->assertRedirect(route('sales.index'));

        $sale = Sale::query()->firstOrFail();
        $this->assertSame(17, $product->fresh()->stock);
        $this->assertSame('37.50', $sale->total);

        $this->actingAs($user)
            ->delete(route('sales.destroy', $sale))
            ->assertRedirect(route('sales.index'));

        $this->assertSame(20, $product->fresh()->stock);
    }

    public function test_completed_order_updates_stock_and_reopening_order_restores_it(): void
    {
        $user = User::factory()->create();
        $product = $this->product(stock: 8);

        $this->actingAs($user)->post(route('orders.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
            'status' => 'pending',
        ])->assertRedirect(route('orders.index'));

        $order = Order::query()->firstOrFail();
        $this->assertSame(8, $product->fresh()->stock);

        $this->actingAs($user)->patch(route('orders.update', $order), [
            'status' => 'completed',
        ])->assertRedirect(route('orders.index'));

        $this->assertSame(6, $product->fresh()->stock);

        $this->actingAs($user)->patch(route('orders.update', $order), [
            'status' => 'processing',
        ])->assertRedirect(route('orders.index'));

        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_customer_can_be_created_updated_and_deleted(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('customers.store'), [
            'name' => 'Sok Dara',
            'email' => 'dara@example.com',
            'phone' => '012345678',
            'address' => 'Phnom Penh',
            'status' => 'active',
        ])->assertRedirect(route('customers.index'));

        $customer = Customer::query()->firstOrFail();

        $this->actingAs($user)->patch(route('customers.update', $customer), [
            'name' => 'Sok Dara Updated',
            'email' => 'dara@example.com',
            'phone' => '012345678',
            'address' => 'Siem Reap',
            'status' => 'active',
        ])->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', ['name' => 'Sok Dara Updated']);

        $this->actingAs($user)
            ->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    private function product(int $stock): Product
    {
        return Product::query()->create([
            'name' => 'Test Product',
            'sku' => 'TEST-'.fake()->unique()->numerify('####'),
            'category' => 'Testing',
            'price' => 10,
            'stock' => $stock,
            'status' => 'active',
        ]);
    }
}
