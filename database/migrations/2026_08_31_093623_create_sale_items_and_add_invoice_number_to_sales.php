<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            $table->string('invoice_number', 40)->nullable()->unique()->after('id');
        });

        Schema::create('sale_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->timestamps();
        });

        DB::table('sales')
            ->orderBy('id')
            ->eachById(function (object $sale): void {
                /** @var array<string, mixed> $row */
                $row = (array) $sale;
                $invoiceNumber = 'INV-'.str_pad((string) $row['id'], 6, '0', STR_PAD_LEFT);

                DB::table('sales')->where('id', $row['id'])->update([
                    'invoice_number' => $invoiceNumber,
                ]);

                DB::table('sale_items')->insert([
                    'sale_id' => $row['id'],
                    'product_id' => $row['product_id'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'total' => $row['total'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');

        Schema::table('sales', function (Blueprint $table): void {
            $table->dropUnique(['invoice_number']);
            $table->dropColumn('invoice_number');
        });
    }
};
