<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\Item;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException;
use Psys\OrderInvoiceBundle\Service\Calculator;
use Psys\OrderInvoiceBundle\Service\Math;
use Psys\OrderInvoiceBundle\Service\OrderManager\OrderManager;
use PHPUnit\Framework\TestCase;

class OrderManagerTest extends TestCase
{
    public function testSavePersistsAndFlushesOrderAndAdvanceTotals(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');
        $manager = $this->createManager($em);

        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);
        $order->setPaymentMode(1);
        $advance = new InvoiceAdvance();
        $advance->setPaymentMode(1);
        $advance->setCurrency('CZK');
        $order->addInvoiceAdvance($advance);
        $item = new Item();
        $item->setAmount(2);
        $item->setPriceVatIncluded('120.00');
        $item->setVatRate('20.00');
        $advance->addItem($item);
        $order->addItem($item);

        $manager->save($order);

        $this->assertSame('240.00', $order->getPriceVatIncluded());
        $this->assertSame('200.00', $order->getPriceVatExcluded());
        $this->assertSame('200.00', $order->getPriceVatBase());
        $this->assertSame('40.00', $order->getPriceVat());
        $this->assertSame('240.00', $advance->getPriceVatIncluded());
    }

    public function testSaveThrowsForAdvanceWithoutPaymentMode(): void
    {
        $manager = $this->createManager($this->createMock(EntityManagerInterface::class));
        $order = $this->createOrderWithAdvance();

        $this->expectException(InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Advance invoice has no payment mode set.');

        $manager->save($order);
    }

    public function testSaveThrowsForAdvanceWithoutCurrency(): void
    {
        $manager = $this->createManager($this->createMock(EntityManagerInterface::class));
        $order = $this->createOrderWithAdvance();
        $order->getInvoicesAdvance()->first()->setPaymentMode(1);
        $order->getInvoicesAdvance()->first()->setCurrency(null);

        $this->expectException(InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Advance invoice has no currency set.');

        $manager->save($order);
    }

    public function testSaveWithProformaInvoice(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');
        $manager = $this->createManager($em);
        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);
        $proforma = new InvoiceProforma();
        $proforma->setPayable(false);
        $proforma->setCurrency('CZK');
        $order->setInvoiceProforma($proforma);
        $item = new Item();
        $item->setAmount(1);
        $item->setPriceVatIncluded('120.00');
        $item->setVatRate('20.00');
        $proforma->addItem($item);
        $order->addItem($item);

        $manager->save($order);

        $this->assertSame('120.00', $proforma->getPriceVatIncluded());
        $this->assertSame('100.00', $proforma->getPriceVatExcluded());
    }

    public function testSaveThrowsForProformaWithoutCurrency(): void
    {
        $manager = $this->createManager($this->createMock(EntityManagerInterface::class));
        $order = $this->createOrderWithProforma(false);

        $this->expectException(InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Proforma invoice has no currency set.');

        $manager->save($order);
    }

    public function testSaveThrowsForPayableProformaWithoutPaymentMode(): void
    {
        $manager = $this->createManager($this->createMock(EntityManagerInterface::class));
        $order = $this->createOrderWithProforma(true);
        $order->getInvoiceProforma()->setCurrency('CZK');

        $this->expectException(InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Proforma invoice is payable and has no payment mode set.');

        $manager->save($order);
    }

    public function testSaveThrowsForProformaWithoutItems(): void
    {
        $manager = $this->createManager($this->createMock(EntityManagerInterface::class));
        $order = new Order();
        $proforma = new InvoiceProforma();
        $proforma->setPayable(false);
        $proforma->setCurrency('CZK');
        $order->setInvoiceProforma($proforma);

        $this->expectException(InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Proforma invoice has no items.');

        $manager->save($order);
    }

    public function testSaveWithEmptyOrderItems(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');
        $manager = $this->createManager($em);
        $order = new Order();
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setState(1);

        $manager->save($order);

        $this->assertSame('0.00', $order->getPriceVatIncluded());
    }

    private function createManager(EntityManagerInterface $em): OrderManager
    {
        return new OrderManager($em, new Calculator(new Math()));
    }

    private function createOrderWithAdvance(): Order
    {
        $order = new Order();
        $advance = new InvoiceAdvance();
        $advance->setCurrency('CZK');
        $item = new Item();
        $item->setAmount(1);
        $item->setPriceVatIncluded('120.00');
        $item->setVatRate('20.00');
        $advance->addItem($item);
        $order->addInvoiceAdvance($advance);

        return $order;
    }

    private function createOrderWithProforma(bool $payable): Order
    {
        $order = new Order();
        $proforma = new InvoiceProforma();
        $proforma->setPayable($payable);
        $item = new Item();
        $item->setAmount(1);
        $item->setPriceVatIncluded('120.00');
        $item->setVatRate('20.00');
        $proforma->addItem($item);
        $order->setInvoiceProforma($proforma);

        return $order;
    }
}
