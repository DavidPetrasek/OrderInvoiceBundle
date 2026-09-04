<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Service;

class Math
{
    public function addPercentage(string $number, string $percentage, int $scale = 2): string
    {
        // Formula: $number * (100 + $percentage) / 100
        $factor = bcadd('100', $percentage, 4);
        $multiplied = bcmul($number, $factor, 4);

        return bcdiv($multiplied, '100', $scale);
    }

    public function subtractPercentage(string $number, string $percentage, int $scale = 2): string
    {
        // Formula: ($number * 100) / (100 + $percentage)
        $divisor = bcadd('100', $percentage, 4);
        $multiplied = bcmul($number, '100', 4);

        return bcdiv($multiplied, $divisor, $scale);
    }
}