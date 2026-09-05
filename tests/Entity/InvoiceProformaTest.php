<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\Item;
use Psys\OrderInvoiceBundle\Entity\File;

class InvoiceProformaTest extends TestCase
{
    public function testInvoiceProformaInitialization(): void
    {
        $invoice = new InvoiceProforma();

        $this->assertNull($invoice->getId());
        $this->assertNull($invoice->getSequentialNumber());
        $this->assertNull($invoice->getReferenceNumber());
        $this->assertNull($invoice->getPaymentReference());
        $this->assertNull($invoice->getDueDate());
        $this->assertFalse($invoice->isPayable());
        $this->assertEmpty($invoice->getItems());
    }

    public function testSetAndGetSequentialNumber(): void
    {
        $invoice = new InvoiceProforma();
        $number = '2024-001';

        $result = $invoice->setSequentialNumber($number);

        $this->assertSame($invoice, $result);
        $this->assertSame($number, $invoice->getSequentialNumber());
    }

    public function testSetAndGetReferenceNumber(): void
    {
        $invoice = new InvoiceProforma();
        $number = 'REF-2024-00001';

        $result = $invoice->setReferenceNumber($number);

        $this->assertSame($invoice, $result);
        $this->assertSame($number, $invoice->getReferenceNumber());
    }

    public function testSetAndGetPaymentReference(): void
    {
        $invoice = new InvoiceProforma();
        $paymentRef = '1234567890';

        $result = $invoice->setPaymentReference($paymentRef);

        $this->assertSame($invoice, $result);
        $this->assertSame($paymentRef, $invoice->getPaymentReference());
    }

    public function testSetPaymentReferenceToNull(): void
    {
        $invoice = new InvoiceProforma();
        $invoice->setPaymentReference('123456');

        $invoice->setPaymentReference(null);

        $this->assertNull($invoice->getPaymentReference());
    }

    public function testSetAndGetCreatedAt(): void
    {
        $invoice = new InvoiceProforma();
        $createdAt = new \DateTimeImmutable('2024-01-15 10:30:00');

        $result = $invoice->setCreatedAt($createdAt);

        $this->assertSame($invoice, $result);
        $this->assertSame($createdAt, $invoice->getCreatedAt());
    }

    public function testSetAndGetDueDate(): void
    {
        $invoice = new InvoiceProforma();
        $dueDate = new \DateTimeImmutable('2024-02-15');

        $result = $invoice->setDueDate($dueDate);

        $this->assertSame($invoice, $result);
        $this->assertSame($dueDate, $invoice->getDueDate());
    }

    public function testSetAndIsPayable(): void
    {
        $invoice = new InvoiceProforma();

        $result = $invoice->setPayable(true);

        $this->assertSame($invoice, $result);
        $this->assertTrue($invoice->isPayable());
    }

    public function testSetFile(): void
    {
        $invoice = new InvoiceProforma();
        $file = new File();
        $file->setMimeType('application/pdf');
        $file->setNameFileSystem('inv_123.pdf');

        $result = $invoice->setFile($file);

        $this->assertSame($invoice, $result);
        $this->assertSame($file, $invoice->getFile());
    }

    public function testAddItem(): void
    {
        $invoice = new InvoiceProforma();
        $item = new Item();
        $item->setName('Test Product');

        $result = $invoice->addItem($item);

        $this->assertSame($invoice, $result);
        $this->assertTrue($invoice->getItems()->contains($item));
        $this->assertSame($invoice, $item->getInvoiceProforma());
    }

    public function testAddMultipleItems(): void
    {
        $invoice = new InvoiceProforma();
        $item1 = new Item();
        $item1->setName('Item 1');

        $item2 = new Item();
        $item2->setName('Item 2');

        $invoice->addItem($item1);
        $invoice->addItem($item2);

        $this->assertCount(2, $invoice->getItems());
        $this->assertTrue($invoice->getItems()->contains($item1));
        $this->assertTrue($invoice->getItems()->contains($item2));
    }

    public function testRemoveItem(): void
    {
        $invoice = new InvoiceProforma();
        $item = new Item();
        $item->setName('Test Product');

        $invoice->addItem($item);
        $this->assertTrue($invoice->getItems()->contains($item));

        $result = $invoice->removeItem($item);

        $this->assertSame($invoice, $result);
        $this->assertFalse($invoice->getItems()->contains($item));
        $this->assertNull($item->getInvoiceProforma());
    }

    public function testDoNotAddDuplicateItems(): void
    {
        $invoice = new InvoiceProforma();
        $item = new Item();

        $invoice->addItem($item);
        $invoice->addItem($item);

        $this->assertCount(1, $invoice->getItems());
    }

    public function testCompleteInvoiceDataFlow(): void
    {
        $invoice = new InvoiceProforma();
        $createdAt = new \DateTimeImmutable('2024-01-10');
        $dueDate = new \DateTimeImmutable('2024-02-10');
        $file = new File();
        $item = new Item();

        $invoice
            ->setSequentialNumber('2024-PROF-001')
            ->setReferenceNumber('REF-2024-001')
            ->setPaymentReference('1111111111')
            ->setCreatedAt($createdAt)
            ->setDueDate($dueDate)
            ->setPayable(true)
            ->setFile($file)
            ->addItem($item);

        $this->assertSame('2024-PROF-001', $invoice->getSequentialNumber());
        $this->assertSame('REF-2024-001', $invoice->getReferenceNumber());
        $this->assertSame('1111111111', $invoice->getPaymentReference());
        $this->assertSame($createdAt, $invoice->getCreatedAt());
        $this->assertSame($dueDate, $invoice->getDueDate());
        $this->assertTrue($invoice->isPayable());
        $this->assertSame($file, $invoice->getFile());
        $this->assertCount(1, $invoice->getItems());
    }
}
