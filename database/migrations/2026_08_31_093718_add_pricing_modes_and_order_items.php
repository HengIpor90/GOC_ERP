<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table): void {
            $table->string('sale_type', 20)->default('general')->after('product_id');
            $table->unsignedInteger('sale_quantity')->default(1)->after('sale_type');
            $table->unsignedInteger('units_per_pack')->default(1)->after('sale_quantity');
            $table->decimal('discount_rate', 5, 2)->default(0)->after('unit_price');
            $table->decimal('subtotal', 12, 2)->nullable()->after('discount_rate');
        });

        DB::table('sale_items')->orderBy('id')->eachById(function (object $item): void {
            /** @var array<string, mixed> $row */
            $row = (array) $item;

            DB::table('sale_items')->where('id', $row['id'])->update([
                'sale_quantity' => $row['quantity'],
                'subtotal' => $row['total'],
            ]);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('payment_status', 20)->default('unpaid')->after('payment_method')->index();
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('sale_type', 20)->default('general');
            $table->unsignedInteger('sale_quantity');
            $table->unsignedInteger('units_per_pack')->default(1);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });

        DB::table('orders')->orderBy('id')->eachById(function (object $order): void {
            /** @var array<string, mixed> $row */
            $row = (array) $order;

            DB::table('order_items')->insert([
                'order_id' => $row['id'],
                'product_id' => $row['product_id'],
                'sale_type' => 'general',
                'sale_quantity' => $row['quantity'],
                'units_per_pack' => 1,
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'discount_rate' => 0,
                'subtotal' => $row['total'],
                'total' => $row['total'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['payment_status']);
            $table->dropColumn('payment_status');
        });

        Schema::table('sale_items', function (Blueprint $table): void {
            $table->dropColumn([
                'sale_type',
                'sale_quantity',
                'units_per_pack',
                'discount_rate',
                'subtotal',
            ]);
        });
    }
};
