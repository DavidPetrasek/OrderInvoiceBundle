<?php

namespace Psys\OrderInvoiceBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Entity\Item;
use Psys\OrderInvoiceBundle\Entity\File;

class InvoiceAdvanceTest extends TestCase
{
    public function testInvoiceAdvanceInitialization(): void
    {
        $invoice = new InvoiceAdvance();

        $this->assertNull($invoice->getId());
        $this->assertNull($invoice->getSequentialNumber());
        $this->assertNull($invoice->getReferenceNumber());
        $this->assertNull($invoice->getPaymentReference());
        $this->assertNull($invoice->getDueDate());
        $this->assertEmpty($invoice->getItems());
    }

    public function testSetAndGetOrder(): void
    {
        $invoice = new InvoiceAdvance();
        $order = new Order();

        $result = $invoice->setOrder($order);

        $this->assertSame($invoice, $result);
        $this->assertSame($order, $invoice->getOrder());
    }

    public function testSetAndGetSequentialNumber(): void
    {
        $invoice = new InvoiceAdvance();
        $number = '2024-ADV-001';

        $result = $invoice->setSequentialNumber($number);

        $this->assertSame($invoice, $result);
        $this->assertSame($number, $invoice->getSequentialNumber());
    }

    public function testSetAndGetReferenceNumber(): void
    {
        $invoice = new InvoiceAdvance();
        $number = 'ADV-REF-2024-00001';

        $result = $invoice->setReferenceNumber($number);

        $this->assertSame($invoice, $result);
        $this->assertSame($number, $invoice->getReferenceNumber());
    }

    public function testSetAndGetPaymentReference(): void
    {
        $invoice = new InvoiceAdvance();
        $paymentRef = '9876543210';

        $result = $invoice->setPaymentReference($paymentRef);

        $this->assertSame($invoice, $result);
        $this->assertSame($paymentRef, $invoice->getPaymentReference());
    }

    public function testSetAndGetCreatedAt(): void
    {
        $invoice = new InvoiceAdvance();
        $createdAt = new \DateTimeImmutable('2024-01-20 14:00:00');

        $result = $invoice->setCreatedAt($createdAt);

        $this->assertSame($invoice, $result);
        $this->assertSame($createdAt, $invoice->getCreatedAt());
    }

    public function testSetAndGetDueDate(): void
    {
        $invoice = new InvoiceAdvance();
        $dueDate = new \DateTimeImmutable('2024-02-20');

        $result = $invoice->setDueDate($dueDate);

        $this->assertSame($invoice, $result);
        $this->assertSame($dueDate, $invoice->getDueDate());
    }

    public function testSetFile(): void
    {
        $invoice = new InvoiceAdvance();
        $file = new File();
        $file->setMimeType('application/pdf');

        $result = $invoice->setFile($file);

        $this->assertSame($invoice, $result);
        $this->assertSame($file, $invoice->getFile());
    }

    public function testAddItem(): void
    {
        $invoice = new InvoiceAdvance();
        $item = new Item();
        $item->setName('Advance Service');

        $result = $invoice->addItem($item);

        $this->assertSame($invoice, $result);
        $this->assertTrue($invoice->getItems()->contains($item));
        $this->assertSame($invoice, $item->getInvoiceAdvance());
    }

    public function testAddMultipleItems(): void
    {
        $invoice = new InvoiceAdvance();
        $item1 = new Item();
        $item1->setName('Service 1');
        $item2 = new Item();
        $item2->setName('Service 2');
        $item3 = new Item();
        $item3->setName('Service 3');

        $invoice->addItem($item1);
        $invoice->addItem($item2);
        $invoice->addItem($item3);

        $this->assertCount(3, $invoice->getItems());
        $this->assertTrue($invoice->getItems()->contains($item1));
        $this->assertTrue($invoice->getItems()->contains($item2));
        $this->assertTrue($invoice->getItems()->contains($item3));
    }

    public function testRemoveItem(): void
    {
        $invoice = new InvoiceAdvance();
        $item = new Item();
        $item->setName('Advance Service');

        $invoice->addItem($item);
        $this->assertTrue($invoice->getItems()->contains($item));

        $result = $invoice->removeItem($item);

        $this->assertSame($invoice, $result);
        $this->assertFalse($invoice->getItems()->contains($item));
        $this->assertNull($item->getInvoiceAdvance());
    }

    public function testRemoveNonExistentItemReturnsInvoice(): void
    {
        $invoice = new InvoiceAdvance();
        $item = new Item();

        $result = $invoice->removeItem($item);

        $this->assertSame($invoice, $result);
    }

    public function testDoNotAddDuplicateItems(): void
    {
        $invoice = new InvoiceAdvance();
        $item = new Item();

        $invoice->addItem($item);
        $invoice->addItem($item);

        $this->assertCount(1, $invoice->getItems());
    }

    public function testCompleteAdvanceInvoiceDataFlow(): void
    {
        $invoice = new InvoiceAdvance();
        $order = new Order();
        $createdAt = new \DateTimeImmutable('2024-01-05');
        $dueDate = new \DateTimeImmutable('2024-02-05');
        $file = new File();
        $item1 = new Item();
        $item2 = new Item();

        $invoice
            ->setOrder($order)
            ->setSequentialNumber('2024-ADV-001')
            ->setReferenceNumber('ADV-REF-2024-001')
            ->setPaymentReference('5555555555')
            ->setCreatedAt($createdAt)
            ->setDueDate($dueDate)
            ->setFile($file)
            ->addItem($item1)
            ->addItem($item2);

        $this->assertSame($order, $invoice->getOrder());
        $this->assertSame('2024-ADV-001', $invoice->getSequentialNumber());
        $this->assertSame('ADV-REF-2024-001', $invoice->getReferenceNumber());
        $this->assertSame('5555555555', $invoice->getPaymentReference());
        $this->assertSame($createdAt, $invoice->getCreatedAt());
        $this->assertSame($dueDate, $invoice->getDueDate());
        $this->assertSame($file, $invoice->getFile());
        $this->assertCount(2, $invoice->getItems());
    }

    public function testItemsCollectionBehavior(): void
    {
        $invoice = new InvoiceAdvance();
        $item1 = new Item();
        $item2 = new Item();

        $this->assertEmpty($invoice->getItems());

        $invoice->addItem($item1);
        $this->assertCount(1, $invoice->getItems());

        $invoice->addItem($item2);
        $this->assertCount(2, $invoice->getItems());

        $invoice->removeItem($item1);
        $this->assertCount(1, $invoice->getItems());
        $this->assertTrue($invoice->getItems()->contains($item2));
    }
}
