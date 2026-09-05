<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Service;

class Math
{
    /**
     * Adds a percentage to a base amount (e.g., Net price -> Gross price with VAT)
     * Formula: $number * (100 + $percentage) / 100
     */
    public function addPercentage(string $number, string $percentage, int $scale = 2): string
    {
        $factor = bcadd('100', $percentage, 4);
        $multiplied = bcmul($number, $factor, 4);

        return bcdiv($multiplied, '100', $scale);
    }

    /**
     * Extracts base amount from a total that already includes percentage (e.g., Gross price with VAT -> Net price)
     * Formula: ($number * 100) / (100 + $percentage)
     */
    public function extractPercentage(string $number, string $percentage, int $scale = 2): string
    {
        $divisor = bcadd('100', $percentage, 4);
        $multiplied = bcmul($number, '100', 4);

        return bcdiv($multiplied, $divisor, $scale);
    }

    /**
     * Subtracts a percentage from a total amount (e.g., applying a 20% discount)
     * Formula: $number * (100 - $percentage) / 100
     */
    public function subtractPercentage(string $number, string $percentage, int $scale = 2): string
    {
        $factor = bcsub('100', $percentage, 4);
        $multiplied = bcmul($number, $factor, 4);

        return bcdiv($multiplied, '100', $scale);
    }
}