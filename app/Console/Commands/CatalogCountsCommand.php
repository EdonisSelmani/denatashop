<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CatalogCountsCommand extends Command
{
    protected $signature = 'catalog:counts {--connection= : Database connection to inspect; defaults to database.default}';

    protected $description = 'Print safe read-only catalog and order counts for the selected database connection.';

    public function handle(): int
    {
        $connectionName = $this->option('connection') ?: config('database.default');
        $connection = DB::connection($connectionName);

        $this->line('Connection: '.$connectionName);
        $this->line('Driver: '.$connection->getDriverName());
        $this->line('Database: '.$connection->getDatabaseName());

        foreach (['categories', 'subcategories', 'products', 'users', 'orders'] as $table) {
            $this->line(strtoupper($table).': '.$this->safeCount($connectionName, $table));
        }

        $this->printBooleanBreakdown($connectionName, 'categories', 'is_active');
        $this->printBooleanBreakdown($connectionName, 'subcategories', 'is_active');
        $this->printBooleanBreakdown($connectionName, 'products', 'is_active');
        $this->printProductRelationshipCounts($connectionName);
        $this->printProductImageCounts($connectionName);

        return self::SUCCESS;
    }

    private function safeCount(string $connectionName, string $table): string
    {
        if (! Schema::connection($connectionName)->hasTable($table)) {
            return 'missing';
        }

        return (string) DB::connection($connectionName)->table($table)->count();
    }

    private function printBooleanBreakdown(string $connectionName, string $table, string $column): void
    {
        if (! Schema::connection($connectionName)->hasTable($table) ||
            ! Schema::connection($connectionName)->hasColumn($table, $column)) {
            return;
        }

        $rows = DB::connection($connectionName)
            ->table($table)
            ->select($column, DB::raw('count(*) as total'))
            ->groupBy($column)
            ->orderBy($column)
            ->get();

        foreach ($rows as $row) {
            $value = (string) $row->{$column};
            $this->line(strtoupper($table).'_'.$column.'_'.$value.': '.$row->total);
        }
    }

    private function printProductRelationshipCounts(string $connectionName): void
    {
        if (! Schema::connection($connectionName)->hasTable('products')) {
            return;
        }

        if (Schema::connection($connectionName)->hasColumn('products', 'category_id')) {
            $this->line('PRODUCTS_WITH_CATEGORY_ID: '.DB::connection($connectionName)->table('products')->whereNotNull('category_id')->count());
            $this->line('PRODUCTS_WITHOUT_CATEGORY_ID: '.DB::connection($connectionName)->table('products')->whereNull('category_id')->count());
        } else {
            $this->line('PRODUCTS_CATEGORY_ID_COLUMN: missing');
        }

        if (Schema::connection($connectionName)->hasColumn('products', 'subcategory_id')) {
            $this->line('PRODUCTS_WITH_SUBCATEGORY_ID: '.DB::connection($connectionName)->table('products')->whereNotNull('subcategory_id')->count());
            $this->line('PRODUCTS_WITHOUT_SUBCATEGORY_ID: '.DB::connection($connectionName)->table('products')->whereNull('subcategory_id')->count());
        }
    }

    private function printProductImageCounts(string $connectionName): void
    {
        if (! Schema::connection($connectionName)->hasTable('products') ||
            ! Schema::connection($connectionName)->hasColumn('products', 'image')) {
            return;
        }

        $paths = DB::connection($connectionName)
            ->table('products')
            ->whereNotNull('image')
            ->pluck('image');

        $missing = $paths->filter(fn ($path) => ! is_file(storage_path('app/public/'.ltrim((string) $path, '/'))));

        $this->line('PRODUCT_IMAGE_REFERENCES: '.$paths->count());
        $this->line('PRODUCT_IMAGE_FILES_MISSING_FROM_STORAGE: '.$missing->count());
    }
}
