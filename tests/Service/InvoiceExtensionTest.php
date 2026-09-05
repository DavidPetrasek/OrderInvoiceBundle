<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\Service;

use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Service\Calculator;
use Psys\OrderInvoiceBundle\Twig\Extension\InvoiceExtension;
use PHPUnit\Framework\TestCase;

class InvoiceExtensionTest extends TestCase
{
    public function testRemainingTotalsFilterDelegatesToCalculator(): void
    {
        $order = new Order();
        $expected = [
            'net' => '50.00',
            'vat' => '10.00',
            'incl' => '60.00',
            'isCredit' => false,
            'creditAmount' => '0.00',
        ];
        $calculator = $this->createMock(Calculator::class);
        $calculator->expects($this->once())
            ->method('calculateRemainingTotals')
            ->with($order)
            ->willReturn($expected);

        $result = (new InvoiceExtension($calculator))->calculateRemainingTotals($order);

        $this->assertSame($expected, $result);
    }
}