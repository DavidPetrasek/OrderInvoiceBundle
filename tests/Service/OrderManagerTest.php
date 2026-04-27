<?php

namespace Psys\OrderInvoiceBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
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
        $invoiceAdvance->setPaymentMode(1);
        $invoiceAdvance->setCurrency('CZK');

        $order->addInvoiceAdvance($invoiceAdvance);

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

    public function testSaveThrowsForAdvanceWithoutPaymentMode(): void
    {
        $math = $this->createMock(Math::class);
        $math->method('subtractPercentage')->willReturn(100.00);
        $math->method('addPercentage')->willReturn(120.00);

        $em = $this->createMock(EntityManagerInterface::class);

        $manager = new OrderManager($em, $math);

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);

        $invoiceAdvance = new InvoiceAdvance();
        $order->addInvoiceAdvance($invoiceAdvance);

        $item = new Item();
        $item->setAmount(1);
        $item->setPriceVatIncluded(120.00);
        $item->setVatRate(20.00);

        $invoiceAdvance->addItem($item);
        $order->addItem($item);

        $this->expectException(\Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Advance invoice has no payment mode set.');

        $manager->save($order);
    }

    public function testSaveThrowsForAdvanceWithoutCurrency(): void
    {
        $math = $this->createMock(Math::class);
        $math->method('subtractPercentage')->willReturn(100.00);
        $math->method('addPercentage')->willReturn(120.00);

        $em = $this->createMock(EntityManagerInterface::class);

        $manager = new OrderManager($em, $math);

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);

        $invoiceAdvance = new InvoiceAdvance();
        $invoiceAdvance->setPaymentMode(1);

        $order->addInvoiceAdvance($invoiceAdvance);

        $item = new Item();
        $item->setAmount(1);
        $item->setPriceVatIncluded(120.00);
        $item->setVatRate(20.00);

        $invoiceAdvance->addItem($item);
        $order->addItem($item);

        $this->expectException(\Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Advance invoice has no currency set.');

        $manager->save($order);
    }

    public function testCalculateTotalsWithMultipleItems(): void
    {
        $math = new Math();
        $em = $this->createMock(EntityManagerInterface::class);
        $manager = new OrderManager($em, $math);

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);

        $item1 = new Item();
        $item1->setAmount(2);
        $item1->setPriceVatIncluded(120.00);
        $item1->setVatRate(20.00);

        $item2 = new Item();
        $item2->setAmount(1);
        $item2->setPriceVatIncluded(240.00);
        $item2->setVatRate(20.00);

        $order->addItem($item1);
        $order->addItem($item2);

        $totals = $manager->calculateTotals($order);

        $this->assertEquals(480.00, $totals['vatIncluded']);
        $this->assertEquals(400.00, $totals['vatExcluded']);
        $this->assertEquals(400.00, $totals['vatBase']);
        $this->assertEquals(80.00, $totals['vat']);
    }

    public function testCalculateTotalsWithZeroVatRate(): void
    {
        $math = new Math();
        $em = $this->createMock(EntityManagerInterface::class);
        $manager = new OrderManager($em, $math);

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);

        $item = new Item();
        $item->setAmount(1);
        $item->setPriceVatIncluded(100.00);
        $item->setVatRate(0.00);

        $order->addItem($item);

        $totals = $manager->calculateTotals($order);

        $this->assertEquals(100.00, $totals['vatIncluded']);
        $this->assertEquals(100.00, $totals['vatExcluded']);
        $this->assertEquals(0.00, $totals['vatBase']);
        $this->assertEquals(0.00, $totals['vat']);
    }

    public function testCalculateItemTotalsFromVatExcluded(): void
    {
        $math = new Math();
        $em = $this->createMock(EntityManagerInterface::class);
        $manager = new OrderManager($em, $math);

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);

        $item = new Item();
        $item->setAmount(1);
        $item->setPriceVatExcluded(100.00);
        $item->setVatRate(20.00);

        $order->addItem($item);

        $totals = $manager->calculateTotals($order);

        $this->assertEquals(120.00, $totals['vatIncluded']);
        $this->assertEquals(100.00, $totals['vatExcluded']);
        $this->assertEquals(100.00, $totals['vatBase']);
        $this->assertEquals(20.00, $totals['vat']);
    }

    public function testGetInvoicesAdvanceTotals(): void
    {
        $math = new Math();
        $em = $this->createMock(EntityManagerInterface::class);
        $manager = new OrderManager($em, $math);

        $order = new Order();

        $advance1 = new InvoiceAdvance();
        $advance1->setPriceVatIncluded(240.00);
        $advance1->setPriceVatExcluded(200.00);
        $advance1->setPriceVatBase(200.00);
        $advance1->setPriceVat(40.00);
        $order->addInvoiceAdvance($advance1);

        $advance2 = new InvoiceAdvance();
        $advance2->setPriceVatIncluded(120.00);
        $advance2->setPriceVatExcluded(100.00);
        $advance2->setPriceVatBase(100.00);
        $advance2->setPriceVat(20.00);
        $order->addInvoiceAdvance($advance2);

        $totals = $manager->getInvoicesAdvanceTotals($order);

        $this->assertEquals(360.00, $totals['vatIncluded']);
        $this->assertEquals(300.00, $totals['vatExcluded']);
        $this->assertEquals(300.00, $totals['vatBase']);
        $this->assertEquals(60.00, $totals['vat']);
    }

    public function testGetInvoicesAdvanceTotalsWithNoAdvances(): void
    {
        $math = new Math();
        $em = $this->createMock(EntityManagerInterface::class);
        $manager = new OrderManager($em, $math);

        $order = new Order();

        $totals = $manager->getInvoicesAdvanceTotals($order);

        $this->assertEquals(0.0, $totals['vatIncluded']);
        $this->assertEquals(0.0, $totals['vatExcluded']);
        $this->assertEquals(0.0, $totals['vatBase']);
        $this->assertEquals(0.0, $totals['vat']);
    }

    public function testSaveWithProformaInvoice(): void
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

        $proforma = new \Psys\OrderInvoiceBundle\Entity\InvoiceProforma();
        $proforma->setPayable(false);
        $proforma->setCurrency('CZK');
        $order->setInvoiceProforma($proforma);

        $item = new Item();
        $item->setAmount(1);
        $item->setPriceVatIncluded(120.00);
        $item->setVatRate(20.00);
        $proforma->addItem($item);

        $order->addItem($item);

        $manager->save($order);

        $this->assertNotNull($order->getPriceVatIncluded());
        $this->assertNotNull($proforma->getPriceVatIncluded());
    }

    public function testSaveThrowsForProformaWithoutCurrency(): void
    {
        $math = $this->createMock(Math::class);
        $math->method('subtractPercentage')->willReturn(100.00);
        $math->method('addPercentage')->willReturn(120.00);

        $em = $this->createMock(EntityManagerInterface::class);

        $manager = new OrderManager($em, $math);

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);

        $proforma = new \Psys\OrderInvoiceBundle\Entity\InvoiceProforma();
        $proforma->setPayable(false);
        $order->setInvoiceProforma($proforma);

        $item = new Item();
        $item->setAmount(1);
        $item->setPriceVatIncluded(120.00);
        $item->setVatRate(20.00);
        $proforma->addItem($item);

        $order->addItem($item);

        $this->expectException(\Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Proforma invoice has no currency set.');

        $manager->save($order);
    }

    public function testSaveThrowsForProformaPayableWithoutPaymentMode(): void
    {
        $math = $this->createMock(Math::class);
        $math->method('subtractPercentage')->willReturn(100.00);
        $math->method('addPercentage')->willReturn(120.00);

        $em = $this->createMock(EntityManagerInterface::class);

        $manager = new OrderManager($em, $math);

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);

        $proforma = new \Psys\OrderInvoiceBundle\Entity\InvoiceProforma();
        $proforma->setPayable(true);
        $proforma->setCurrency('CZK');
        $order->setInvoiceProforma($proforma);

        $item = new Item();
        $item->setAmount(1);
        $item->setPriceVatIncluded(120.00);
        $item->setVatRate(20.00);
        $proforma->addItem($item);

        $order->addItem($item);

        $this->expectException(\Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('This proforma invoice is payable and has no payment mode set.');

        $manager->save($order);
    }

    public function testSaveThrowsForProformaWithoutItems(): void
    {
        $math = $this->createMock(Math::class);

        $em = $this->createMock(EntityManagerInterface::class);

        $manager = new OrderManager($em, $math);

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);

        $proforma = new \Psys\OrderInvoiceBundle\Entity\InvoiceProforma();
        $proforma->setPayable(false);
        $proforma->setCurrency('CZK');
        $order->setInvoiceProforma($proforma);

        $this->expectException(\Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Proforma invoice has no items.');

        $manager->save($order);
    }

    public function testSaveWithEmptyOrderItems(): void
    {
        $math = $this->createMock(Math::class);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $manager = new OrderManager($em, $math);

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);

        $manager->save($order);

        // When there are no items, price totals are not calculated
        $this->assertEquals(0.0, $order->getPriceVatIncluded());
    }

    public function testCalculateTotalsWithDifferentVatRates(): void
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

        $item2 = new Item();
        $item2->setAmount(1);
        $item2->setPriceVatIncluded(107.00);
        $item2->setVatRate(7.00);

        $order->addItem($item1);
        $order->addItem($item2);

        $totals = $manager->calculateTotals($order);

        $this->assertEquals(227.00, $totals['vatIncluded']);
    }
}
