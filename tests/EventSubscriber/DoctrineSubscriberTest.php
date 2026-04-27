<?php

namespace Psys\OrderInvoiceBundle\Tests\EventSubscriber;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\EventSubscriber\DoctrineSubscriber;

class DoctrineSubscriberTest extends TestCase
{
    private function createOnFlushEventArgs(array $insertions = [], array $updates = []): OnFlushEventArgs
    {
        $uow = $this->createMock(UnitOfWork::class);
        $uow->method('getScheduledEntityInsertions')->willReturn($insertions);
        $uow->method('getScheduledEntityUpdates')->willReturn($updates);

        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->method('getUnitOfWork')->willReturn($uow);

        $eventArgs = $this->createMock(OnFlushEventArgs::class);
        $eventArgs->method('getObjectManager')->willReturn($manager);

        return $eventArgs;
    }

    public function testFinalWithoutProformaOrAdvanceThrowsException(): void
    {
        $order = new Order();
        $order->setInvoiceFinal(new InvoiceFinal());

        $subscriber = new DoctrineSubscriber();

        $this->expectException(\Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Final invoice requires proforma or advance invoice to be issued first.');

        $subscriber->onFlush($this->createOnFlushEventArgs([$order]));
    }

    public function testProformaAndAdvanceThrowsException(): void
    {
        $order = new Order();
        $order->setInvoiceProforma(new InvoiceProforma());

        $advance = new InvoiceAdvance();
        $order->addInvoiceAdvance($advance);

        $subscriber = new DoctrineSubscriber();

        $this->expectException(\Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Proforma and advance invoice cannot be issued simultaneously.');

        $subscriber->onFlush($this->createOnFlushEventArgs([$order]));
    }

    public function testProformaOnlyDoesNotThrow(): void
    {
        $order = new Order();
        $order->setInvoiceProforma(new InvoiceProforma());

        $subscriber = new DoctrineSubscriber();
        $subscriber->onFlush($this->createOnFlushEventArgs([$order]));

        $this->assertTrue(true);
    }

    public function testUpdateFinalWithoutProformaOrAdvanceThrowsException(): void
    {
        $order = new Order();
        $order->setInvoiceFinal(new InvoiceFinal());

        $subscriber = new DoctrineSubscriber();

        $this->expectException(\Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Final invoice requires proforma or advance invoice to be issued first.');

        $subscriber->onFlush($this->createOnFlushEventArgs([], [$order]));
    }
}
