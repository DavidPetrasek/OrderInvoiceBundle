<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Service\Math;

class MathTest extends TestCase
{
    public function testAddPercentage(): void
    {
        $math = new Math();

        $result = $math->addPercentage('100.00', '20.00');

        $this->assertSame('120.00', $result);
    }

    public function testAddPercentageUsesRequestedScale(): void
    {
        $math = new Math();

        $result = $math->addPercentage('100.00', '12.50', 4);

        $this->assertSame('112.5000', $result);
    }

    public function testExtractPercentage(): void
    {
        $math = new Math();

        $result = $math->extractPercentage('120.00', '20.00');

        $this->assertSame('100.00', $result);
    }

    public function testExtractPercentageUsesRequestedScale(): void
    {
        $math = new Math();

        $result = $math->extractPercentage('100.00', '20.00', 4);

        $this->assertSame('83.3333', $result);
    }

    public function testSubtractPercentage(): void
    {
        $math = new Math();

        $result = $math->subtractPercentage('100.00', '20.00');

        $this->assertSame('80.00', $result);
    }

    public function testSubtractPercentageUsesRequestedScale(): void
    {
        $math = new Math();

        $result = $math->subtractPercentage('100.00', '12.50', 4);

        $this->assertSame('87.5000', $result);
    }
}