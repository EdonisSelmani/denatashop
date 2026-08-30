<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NormalizeDiscountsCommand extends Command
{
    private const MIN_DISCOUNT = 20.0;

    private const MAX_DISCOUNT = 40.0;

    protected $signature = 'products:normalize-discounts {--dry-run : Report changes without updating compare_price}';

    protected $description = 'Normalize existing product compare_price discounts into the inclusive 20% to 40% range without changing selling prices.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $products = Product::query()
            ->whereNotNull('compare_price')
            ->orderBy('id')
            ->get(['id', 'sku', 'name', 'price', 'compare_price']);

        $analysis = $this->analyze($products);
        $affectedRows = $analysis['affected'];

        $this->line('Connection: '.config('database.default'));
        $this->line('Mode: '.($dryRun ? 'dry-run' : 'apply'));
        $this->line('Selling price field: products.price (unchanged)');
        $this->line('Discount field: products.compare_price');
        $this->line('Allowed discount range: '.self::MIN_DISCOUNT.'% through '.self::MAX_DISCOUNT.'%');
        $this->line('Total products: '.Product::query()->count());
        $this->line('Products with compare_price: '.$products->count());
        $this->line('Valid discount min: '.$this->formatPercent($analysis['min_discount']));
        $this->line('Valid discount max: '.$this->formatPercent($analysis['max_discount']));
        $this->line('Discounts below 20%: '.$analysis['below_minimum']->count());
        $this->line('Discounts above 40%: '.$analysis['above_maximum']->count());
        $this->line('Invalid compare_price <= price: '.$analysis['invalid']->count());
        $this->line('Unsafe zero/null/negative price or compare_price rows: '.$analysis['unsafe']->count());
        $this->line('Already inside valid discount range: '.$analysis['inside_range']->count());
        $this->line('Affected products: '.$affectedRows->count());

        if ($analysis['unsafe']->isNotEmpty()) {
            $this->error('Unsafe discount rows found. These rows are reported but not changed:');
            $this->table(
                ['ID', 'SKU', 'Name', 'Price', 'Compare price'],
                $analysis['unsafe']->take(12)->map(fn (Product $product): array => [
                    $product->id,
                    $product->sku,
                    $product->name,
                    $this->formatPrice($product->price),
                    $this->formatPrice($product->compare_price),
                ])->all()
            );
        }

        if ($analysis['invalid']->isNotEmpty()) {
            $this->warn('Invalid or stale compare_price rows found and included in the dry-run plan:');
            $this->table(
                ['ID', 'SKU', 'Name', 'Price', 'Compare price'],
                $analysis['invalid']->take(12)->map(fn (Product $product): array => [
                    $product->id,
                    $product->sku,
                    $product->name,
                    $this->formatPrice($product->price),
                    $this->formatPrice($product->compare_price),
                ])->all()
            );
        }

        if ($affectedRows->isEmpty()) {
            $this->info('No existing discounts need normalization.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'SKU', 'Name', 'Price', 'Old compare', 'Old discount', 'New compare', 'New discount', 'Reason'],
            $affectedRows->map(fn (array $row): array => [
                $row['id'],
                $row['sku'],
                $row['name'],
                $row['price'],
                $row['old_compare_price'],
                $row['old_discount'],
                $row['new_compare_price'],
                $row['new_discount'],
                $row['reason'],
            ])->all()
        );

        if ($dryRun) {
            $this->warn('Dry run only. No compare_price values were changed.');

            return self::SUCCESS;
        }

        if ($analysis['unsafe']->isNotEmpty()) {
            $this->error('Real update stopped because unsafe discount rows were found.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($affectedRows): void {
            foreach ($affectedRows as $row) {
                DB::table('products')
                    ->where('id', $row['id'])
                    ->where('price', $row['price'])
                    ->where('compare_price', $row['old_compare_price'])
                    ->update(['compare_price' => $row['new_compare_price']]);
            }
        });

        $this->info('Normalized '.$affectedRows->count().' product discounts.');

        return self::SUCCESS;
    }

    private function analyze(Collection $products): array
    {
        $validDiscounts = collect();
        $belowMinimum = collect();
        $aboveMaximum = collect();
        $invalid = collect();
        $unsafe = collect();
        $insideRange = collect();
        $affected = collect();

        foreach ($products as $product) {
            $price = (float) $product->price;
            $comparePrice = (float) $product->compare_price;

            if ($price <= 0 || $comparePrice <= 0) {
                $unsafe->push($product);
                continue;
            }

            if ($comparePrice <= $price) {
                $invalid->push($product);
                $affected->push($this->affectedRow($product, self::MIN_DISCOUNT, 'invalid_compare_price'));
                continue;
            }

            $discount = $this->discountPercent($price, $comparePrice);
            $validDiscounts->push($discount);

            if ($discount < self::MIN_DISCOUNT) {
                $belowMinimum->push($product);
                $affected->push($this->affectedRow($product, self::MIN_DISCOUNT, 'below_minimum'));
                continue;
            }

            if ($discount > self::MAX_DISCOUNT) {
                $aboveMaximum->push($product);
                $affected->push($this->affectedRow($product, self::MAX_DISCOUNT, 'above_maximum'));
                continue;
            }

            $insideRange->push($product);
        }

        return [
            'min_discount' => $validDiscounts->isEmpty() ? null : $validDiscounts->min(),
            'max_discount' => $validDiscounts->isEmpty() ? null : $validDiscounts->max(),
            'below_minimum' => $belowMinimum,
            'above_maximum' => $aboveMaximum,
            'invalid' => $invalid,
            'unsafe' => $unsafe,
            'inside_range' => $insideRange,
            'affected' => $affected,
        ];
    }

    private function affectedRow(Product $product, float $targetDiscount, string $reason): array
    {
        $price = (float) $product->price;
        $comparePrice = (float) $product->compare_price;
        $newComparePrice = $this->normalizedComparePrice($price, $targetDiscount);

        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'price' => $this->formatPrice($product->price),
            'old_compare_price' => $this->formatPrice($product->compare_price),
            'old_discount' => $comparePrice > 0 && $comparePrice > $price
                ? $this->formatPercent($this->discountPercent($price, $comparePrice))
                : 'invalid',
            'new_compare_price' => $newComparePrice,
            'new_discount' => $this->formatPercent($this->discountPercent($price, (float) $newComparePrice)),
            'reason' => $reason,
        ];
    }

    private function normalizedComparePrice(float $price, float $targetDiscount): string
    {
        $raw = $price / (1 - ($targetDiscount / 100));
        $retailPrice = $this->nearbyRetailPrice($raw, $price, $targetDiscount);

        if ($retailPrice !== null) {
            return $this->formatPrice($retailPrice);
        }

        $rounded = $targetDiscount === self::MAX_DISCOUNT
            ? floor($raw * 100) / 100
            : ceil($raw * 100) / 100;

        return $this->formatPrice(max($rounded, $price + 0.01));
    }

    private function nearbyRetailPrice(float $raw, float $price, float $targetDiscount): ?float
    {
        $candidates = collect();
        $start = max(0, (int) floor($raw) - 2);
        $end = (int) ceil($raw) + 2;

        foreach (range($start, $end) as $whole) {
            foreach ([0.00, 0.50, 0.90, 0.99] as $ending) {
                $candidate = round($whole + $ending, 2);
                $discount = $this->discountPercent($price, $candidate);

                if (
                    $candidate > $price
                    && abs($candidate - $raw) <= 0.25
                    && abs($discount - $targetDiscount) <= 0.25
                    && $discount >= self::MIN_DISCOUNT
                    && $discount <= self::MAX_DISCOUNT
                ) {
                    $candidates->push($candidate);
                }
            }
        }

        return $candidates
            ->unique()
            ->sortBy(fn (float $candidate): float => abs($candidate - $raw))
            ->first();
    }

    private function discountPercent(float $price, float $comparePrice): float
    {
        if ($comparePrice <= 0) {
            return 0.0;
        }

        return (($comparePrice - $price) / $comparePrice) * 100;
    }

    private function formatPrice(mixed $price): string
    {
        return number_format((float) $price, 2, '.', '');
    }

    private function formatPercent(?float $percent): string
    {
        return $percent === null ? 'n/a' : number_format($percent, 2, '.', '').'%';
    }
}
