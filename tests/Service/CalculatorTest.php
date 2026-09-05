<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\Service;

use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\Item;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Service\Calculator;
use Psys\OrderInvoiceBundle\Service\Math;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    public function testCalculateTotalsFromItemVatIncluded(): void
    {
        $calculator = new Calculator(new Math());
        $order = new Order();
        $item = new Item();
        $item->setAmount(1);
        $item->setPriceVatIncluded('120.00');
        $item->setVatRate('20.00');
        $order->addItem($item);

        $totals = $calculator->calculateTotals($order);

        $this->assertSame('120.00', $totals['vatIncluded']);
        $this->assertSame('100.00', $totals['vatExcluded']);
        $this->assertSame('100.00', $totals['vatBase']);
        $this->assertSame('20.00', $totals['vat']);
    }

    public function testCalculateTotalsFromVatExcluded(): void
    {
        $calculator = new Calculator(new Math());
        $order = new Order();
        $item = new Item();
        $item->setAmount(1);
        $item->setPriceVatExcluded('100.00');
        $item->setVatRate('20.00');
        $order->addItem($item);

        $totals = $calculator->calculateTotals($order);

        $this->assertSame('120.00', $totals['vatIncluded']);
        $this->assertSame('100.00', $totals['vatExcluded']);
        $this->assertSame('100.00', $totals['vatBase']);
        $this->assertSame('20.00', $totals['vat']);
    }

    public function testCalculateTotalsWithMultipleItemsAndVatRates(): void
    {
        $calculator = new Calculator(new Math());
        $order = new Order();

        $item1 = new Item();
        $item1->setAmount(2);
        $item1->setPriceVatIncluded('120.00');
        $item1->setVatRate('20.00');
        $order->addItem($item1);

        $item2 = new Item();
        $item2->setAmount(1);
        $item2->setPriceVatIncluded('107.00');
        $item2->setVatRate('7.00');
        $order->addItem($item2);

        $totals = $calculator->calculateTotals($order);

        $this->assertSame('347.00', $totals['vatIncluded']);
        $this->assertSame('300.00', $totals['vatExcluded']);
        $this->assertSame('300.00', $totals['vatBase']);
        $this->assertSame('47.00', $totals['vat']);
    }

    public function testCalculateTotalsExcludesZeroVatItemsFromVatBase(): void
    {
        $calculator = new Calculator(new Math());
        $order = new Order();
        $item = new Item();
        $item->setAmount(1);
        $item->setPriceVatIncluded('100.00');
        $item->setVatRate('0.00');
        $order->addItem($item);

        $totals = $calculator->calculateTotals($order);

        $this->assertSame('100.00', $totals['vatIncluded']);
        $this->assertSame('100.00', $totals['vatExcluded']);
        $this->assertSame('0.00', $totals['vatBase']);
        $this->assertSame('0.00', $totals['vat']);
    }

    public function testGetInvoicesAdvanceTotals(): void
    {
        $calculator = new Calculator(new Math());
        $order = new Order();

        foreach ([['240.00', '200.00', '40.00'], ['120.00', '100.00', '20.00']] as $values)
        {
            $advance = new InvoiceAdvance();
            $advance->setPriceVatIncluded($values[0]);
            $advance->setPriceVatExcluded($values[1]);
            $advance->setPriceVatBase($values[1]);
            $advance->setPriceVat($values[2]);
            $order->addInvoiceAdvance($advance);
        }

        $totals = $calculator->getInvoicesAdvanceTotals($order);

        $this->assertSame('360.00', $totals['vatIncluded']);
        $this->assertSame('300.00', $totals['vatExcluded']);
        $this->assertSame('300.00', $totals['vatBase']);
        $this->assertSame('60.00', $totals['vat']);
    }

    public function testCalculateRemainingTotalsWithoutDeductions(): void
    {
        $calculator = new Calculator(new Math());
        $order = $this->createOrderTotals('120.00', '100.00', '20.00');

        $remaining = $calculator->calculateRemainingTotals($order);

        $this->assertSame(['net' => '100.00', 'vat' => '20.00', 'incl' => '120.00', 'isCredit' => false, 'creditAmount' => '0.00'], $remaining);
    }

    public function testCalculateRemainingTotalsDeductsPayableProforma(): void
    {
        $calculator = new Calculator(new Math());
        $order = $this->createOrderTotals('120.00', '100.00', '20.00');
        $proforma = new InvoiceProforma();
        $proforma->setPayable(true);
        $proforma->setPriceVatIncluded('60.00');
        $proforma->setPriceVatExcluded('50.00');
        $proforma->setPriceVat('10.00');
        $order->setInvoiceProforma($proforma);

        $remaining = $calculator->calculateRemainingTotals($order);

        $this->assertSame('50.00', $remaining['net']);
        $this->assertSame('10.00', $remaining['vat']);
        $this->assertSame('60.00', $remaining['incl']);
        $this->assertFalse($remaining['isCredit']);
        $this->assertSame('0.00', $remaining['creditAmount']);
    }

    public function testCalculateRemainingTotalsPrefersAdvancesToProforma(): void
    {
        $calculator = new Calculator(new Math());
        $order = $this->createOrderTotals('120.00', '100.00', '20.00');
        $advance = new InvoiceAdvance();
        $advance->setPriceVatIncluded('30.00');
        $advance->setPriceVatExcluded('25.00');
        $advance->setPriceVatBase('25.00');
        $advance->setPriceVat('5.00');
        $order->addInvoiceAdvance($advance);
        $proforma = new InvoiceProforma();
        $proforma->setPayable(true);
        $proforma->setPriceVatIncluded('60.00');
        $proforma->setPriceVatExcluded('50.00');
        $proforma->setPriceVat('10.00');
        $order->setInvoiceProforma($proforma);

        $remaining = $calculator->calculateRemainingTotals($order);

        $this->assertSame('75.00', $remaining['net']);
        $this->assertSame('15.00', $remaining['vat']);
        $this->assertSame('90.00', $remaining['incl']);
    }

    public function testCalculateRemainingTotalsReportsCreditWhenDeductionsExceedOrder(): void
    {
        $calculator = new Calculator(new Math());
        $order = $this->createOrderTotals('100.00', '83.33', '16.67');
        $advance = new InvoiceAdvance();
        $advance->setPriceVatIncluded('120.00');
        $advance->setPriceVatExcluded('100.00');
        $advance->setPriceVatBase('100.00');
        $advance->setPriceVat('20.00');
        $order->addInvoiceAdvance($advance);

        $remaining = $calculator->calculateRemainingTotals($order);

        $this->assertSame('-20.00', $remaining['incl']);
        $this->assertTrue($remaining['isCredit']);
        $this->assertSame('20.00', $remaining['creditAmount']);
    }

    private function createOrderTotals(string $included, string $excluded, string $vat): Order
    {
        $order = new Order();
        $order->setPriceVatIncluded($included);
        $order->setPriceVatExcluded($excluded);
        $order->setPriceVat($vat);

        return $order;
    }
}