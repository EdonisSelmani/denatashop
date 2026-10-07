<?php

namespace Tests\Unit;

use App\Services\PriceNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PriceNormalizerTest extends TestCase
{
    #[DataProvider('prices')]
    public function test_it_rounds_to_the_nearest_five_cents_without_floats(string $input, string $expected): void
    {
        $this->assertSame($expected, (new PriceNormalizer)->normalize($input));
    }

    public static function prices(): array
    {
        return [
            ['0.29', '1.50'],
            ['0.85', '1.50'],
            ['1.15', '1.50'],
            ['1.45', '1.50'],
            ['1.50', '1.50'],
            ['1.63', '1.65'],
            ['1.62', '1.60'],
            ['2.18', '2.20'],
            ['2.17', '2.15'],
            ['5.66', '5.65'],
            ['5.68', '5.70'],
            ['10.00', '10.00'],
            ['10.05', '10.05'],
            ['0.01', '1.50'],
            ['0.02', '1.50'],
            ['0.03', '1.50'],
        ];
    }
}
