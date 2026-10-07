<?php

namespace App\Services;

use InvalidArgumentException;

class PriceNormalizer
{
    public const INCREMENT_CENTS = 5;

    public const MINIMUM_SELLING_PRICE_CENTS = 150;

    public function normalize(string|int $amount): string
    {
        return $this->formatCents($this->normalizeCents($this->toCents($amount)));
    }

    public function normalizeCents(int $cents): int
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Money amounts cannot be negative.');
        }

        $normalized = intdiv($cents + intdiv(self::INCREMENT_CENTS, 2), self::INCREMENT_CENTS)
            * self::INCREMENT_CENTS;

        return max(self::MINIMUM_SELLING_PRICE_CENTS, $normalized);
    }

    public function toCents(string|int $amount): int
    {
        $amount = trim((string) $amount);

        if (! preg_match('/^(\d+)(?:\.(\d{1,2}))?$/', $amount, $matches)) {
            throw new InvalidArgumentException("Invalid money amount: {$amount}");
        }

        $whole = (int) $matches[1];
        $fraction = str_pad($matches[2] ?? '', 2, '0');

        return ($whole * 100) + (int) $fraction;
    }

    public function formatCents(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public function isNormalized(string|int $amount): bool
    {
        $cents = $this->toCents($amount);

        return $cents >= self::MINIMUM_SELLING_PRICE_CENTS
            && $cents % self::INCREMENT_CENTS === 0;
    }
}
