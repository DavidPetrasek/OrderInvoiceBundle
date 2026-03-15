<?php

namespace Psys\OrderInvoiceBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psys\OrderInvoiceBundle\Entity\Invoice;
use Psys\OrderInvoiceBundle\Entity\Item;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Service\OrderManager\OrderManager;
use Psys\Utils\Math;
use PHPUnit\Framework\TestCase;

class OrderManagerTest extends TestCase
{
    public function testCalculateTotalsFromItemVatIncluded(): void
    {
        $math = new Math();
        $em = $this->createMock(EntityManagerInterface::class);
        $manager = new OrderManager($em, $math);

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);

        $item1 = new Item();
        $item1->setAmount(1);
        $item1->setPriceVatIncluded(120.00);
        $item1->setVatRate(20.00);

        $order->addItem($item1);

        $totals = $manager->calculateTotals($order);

        $this->assertEquals(120.00, $totals['vatIncluded']);
        $this->assertEquals(100.00, $totals['vatExcluded']);
        $this->assertEquals(100.00, $totals['vatBase']);
        $this->assertEquals(20.00, $totals['vat']);
    }

    public function testSavePersistsAndFlushesOrderAndAdvancesData(): void
    {
        $math = $this->createMock(Math::class);
        $math->method('subtractPercentage')->willReturn(100.00);
        $math->method('addPercentage')->willReturn(120.00);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $manager = new OrderManager($em, $math);

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);
        $order->setPaymentMode(1);

        $invoiceAdvance = new InvoiceAdvance();
        $invoice = new Invoice();
        $invoice->addInvoiceAdvance($invoiceAdvance);
        $order->setInvoice($invoice);

        $item = new Item();
        $item->setAmount(2);
        $item->setPriceVatIncluded(120.00);
        $item->setVatRate(20.00);
        $invoiceAdvance->addItem($item);

        $order->addItem($item);

        $manager->save($order);

        $this->assertSame(240.0, $order->getPriceVatIncluded());
        $this->assertSame(200.0, $order->getPriceVatExcluded());
        $this->assertSame(200.0, $order->getPriceVatBase());
        $this->assertSame(40.0, $order->getPriceVat());
    }
}
