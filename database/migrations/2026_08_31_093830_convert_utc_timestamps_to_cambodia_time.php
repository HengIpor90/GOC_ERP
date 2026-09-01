<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, array<int, string>> */
    private array $timestampColumns = [
        'users' => ['created_at', 'updated_at'],
        'products' => ['created_at', 'updated_at'],
        'customers' => ['created_at', 'updated_at'],
        'stock_movements' => ['created_at', 'updated_at'],
        'sales' => ['sold_at', 'created_at', 'updated_at'],
        'sale_items' => ['created_at', 'updated_at'],
        'orders' => ['ordered_at', 'created_at', 'updated_at'],
        'order_items' => ['created_at', 'updated_at'],
    ];

    public function up(): void
    {
        $this->shiftTimestamps(7);
    }

    public function down(): void
    {
        $this->shiftTimestamps(-7);
    }

    private function shiftTimestamps(int $hours): void
    {
        foreach ($this->timestampColumns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $availableColumns = array_values(array_filter(
                $columns,
                fn (string $column): bool => Schema::hasColumn($table, $column),
            ));

            if ($availableColumns === []) {
                continue;
            }

            DB::table($table)
                ->select(array_merge(['id'], $availableColumns))
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($table, $availableColumns, $hours): void {
                    foreach ($rows as $row) {
                        $updates = [];

                        foreach ($availableColumns as $column) {
                            $value = $row->{$column};

                            if ($value !== null) {
                                $updates[$column] = CarbonImmutable::parse((string) $value, 'UTC')
                                    ->addHours($hours)
                                    ->format('Y-m-d H:i:s');
                            }
                        }

                        if ($updates !== []) {
                            DB::table($table)->where('id', $row->id)->update($updates);
                        }
                    }
                });
        }
    }
};
