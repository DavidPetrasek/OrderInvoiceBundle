<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\EventSubscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceRegular;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\EventSubscriber\DoctrineSubscriber;
use Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException;

class DoctrineSubscriberTest extends TestCase
{
    private MockObject $entityManager;
    private MockObject $unitOfWork;
    private MockObject $eventArgs;
    private DoctrineSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->unitOfWork = $this->createMock(UnitOfWork::class);
        $this->eventArgs = $this->createMock(OnFlushEventArgs::class);

        $this->eventArgs->method('getObjectManager')->willReturn($this->entityManager);
        $this->entityManager->method('getUnitOfWork')->willReturn($this->unitOfWork);

        $this->subscriber = new DoctrineSubscriber();
    }

    /**
     * Helper to configure UnitOfWork scheduled entities
     */
    private function setupUow(array $insertions = [], array $updates = []): void
    {
        $this->unitOfWork->method('getScheduledEntityInsertions')->willReturn($insertions);
        $this->unitOfWork->method('getScheduledEntityUpdates')->willReturn($updates);
    }

    public function testOnFlushThrowsExceptionWhenFinalInvoiceInsertedWithoutPriorInvoices(): void
    {
        $order = $this->createMock(Order::class);
        $order->method('getInvoiceFinal')->willReturn(new InvoiceFinal());
        $order->method('getInvoicesAdvance')->willReturn(new ArrayCollection());
        $order->method('getInvoiceProforma')->willReturn(null);

        $this->setupUow([$order]);

        $this->expectException(InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Final invoice requires proforma or advance invoice to be issued first.');

        $this->subscriber->onFlush($this->eventArgs);
    }

    public function testOnFlushThrowsExceptionWhenFinalInvoiceInsertedOnEmptyOrder(): void
    {
        $order = $this->createMock(Order::class);
        $finalInvoice = new InvoiceFinal();

        // Mocking requirements for the "Final invoice issuance" check
        $order->method('getInvoicesAdvance')->willReturn(new ArrayCollection([new InvoiceAdvance()]));
        $order->method('getInvoiceProforma')->willReturn(null);
        $order->method('getItems')->willReturn(new ArrayCollection()); // Empty items

        // We need both the Order updated and the Final Invoice inserted
        $this->setupUow([$finalInvoice], [$order]);

        $this->expectException(InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Final invoice requires the order to have at least one item (which represents the total price).');

        $this->subscriber->onFlush($this->eventArgs);
    }

    public function testSimultaneousProformaAndAdvanceThrowsException(): void
    {
        $this->setupUow([
            new InvoiceProforma(),
            new InvoiceAdvance()
        ]);

        $this->expectException(InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Proforma and advance invoice cannot be issued simultaneously.');

        $this->subscriber->onFlush($this->eventArgs);
    }

    public function testSimultaneousProformaAndRegularThrowsException(): void
    {
        $this->setupUow([
            new InvoiceProforma(),
            new InvoiceRegular()
        ]);

        $this->expectException(InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Proforma and regular invoice cannot be issued simultaneously.');

        $this->subscriber->onFlush($this->eventArgs);
    }

    public function testSimultaneousAdvanceAndRegularThrowsException(): void
    {
        $this->setupUow([
            new InvoiceAdvance(),
            new InvoiceRegular()
        ]);

        $this->expectException(InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Advance and regular invoice cannot be issued simultaneously.');

        $this->subscriber->onFlush($this->eventArgs);
    }

    public function testSimultaneousFinalAndProformaThrowsException(): void
    {
        $this->setupUow([
            new InvoiceFinal(),
            new InvoiceProforma()
        ]);

        $this->expectException(InvalidInvoiceStateException::class);
        $this->expectExceptionMessage('Final and proforma invoice cannot be issued simultaneously.');

        $this->subscriber->onFlush($this->eventArgs);
    }

    public function testSuccessfulOnFlushWithValidOrder(): void
    {
        $order = $this->createMock(Order::class);
        // Valid state: Order inserted, no invoices yet
        $order->method('getInvoiceFinal')->willReturn(null);
        $order->method('getInvoicesAdvance')->willReturn(new ArrayCollection());
        $order->method('getInvoiceProforma')->willReturn(null);

        $this->setupUow([$order]);

        // Should not throw any exception
        $this->subscriber->onFlush($this->eventArgs);
        $this->assertTrue(true); 
    }
}
