<?php

declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;
use Psys\OrderInvoiceBundle\Entity\File;

class InvoiceFinalTest extends TestCase
{
    public function testInvoiceFinalInitialization(): void
    {
        $invoice = new InvoiceFinal();

        $this->assertNull($invoice->getId());
        $this->assertNull($invoice->getSequentialNumber());
        $this->assertNull($invoice->getReferenceNumber());
        $this->assertNull($invoice->getPaymentReference());
        $this->assertNull($invoice->getDueDate());
        $this->assertFalse($invoice->isPaid());
        $this->assertNull($invoice->getPaidAt());
    }

    public function testSetAndGetSequentialNumber(): void
    {
        $invoice = new InvoiceFinal();
        $number = '2024-FIN-001';

        $result = $invoice->setSequentialNumber($number);

        $this->assertSame($invoice, $result);
        $this->assertSame($number, $invoice->getSequentialNumber());
    }

    public function testSetAndGetReferenceNumber(): void
    {
        $invoice = new InvoiceFinal();
        $number = 'FIN-REF-2024-00001';

        $result = $invoice->setReferenceNumber($number);

        $this->assertSame($invoice, $result);
        $this->assertSame($number, $invoice->getReferenceNumber());
    }

    public function testSetAndGetPaymentReference(): void
    {
        $invoice = new InvoiceFinal();
        $reference = '3521234567890123456789';

        $result = $invoice->setPaymentReference($reference);

        $this->assertSame($invoice, $result);
        $this->assertSame($reference, $invoice->getPaymentReference());
    }

    public function testSetAndGetDueDate(): void
    {
        $invoice = new InvoiceFinal();
        $dueDate = new \DateTimeImmutable('2024-02-15');

        $result = $invoice->setDueDate($dueDate);

        $this->assertSame($invoice, $result);
        $this->assertSame($dueDate, $invoice->getDueDate());
    }

    public function testSetAndGetCreatedAt(): void
    {
        $invoice = new InvoiceFinal();
        $createdAt = new \DateTimeImmutable('2024-01-15 10:00:00');

        $result = $invoice->setCreatedAt($createdAt);

        $this->assertSame($invoice, $result);
        $this->assertSame($createdAt, $invoice->getCreatedAt());
    }

    public function testSetAndGetFile(): void
    {
        $invoice = new InvoiceFinal();
        $file = new File();
        $file->setNameDisplay('Invoice_Final_2024.pdf');

        $result = $invoice->setFile($file);

        $this->assertSame($invoice, $result);
        $this->assertSame($file, $invoice->getFile());
    }

    public function testSetPaidWithTrue(): void
    {
        $invoice = new InvoiceFinal();

        $result = $invoice->setPaid(true);

        $this->assertSame($invoice, $result);
        $this->assertTrue($invoice->isPaid());
        $this->assertInstanceOf(\DateTimeImmutable::class, $invoice->getPaidAt());
    }

    public function testSetPaidWithFalse(): void
    {
        $invoice = new InvoiceFinal();
        $invoice->setPaid(true);
        $this->assertTrue($invoice->isPaid());

        $result = $invoice->setPaid(false);

        $this->assertSame($invoice, $result);
        $this->assertFalse($invoice->isPaid());
        $this->assertNull($invoice->getPaidAt());
    }

    public function testSetAndGetPaidAt(): void
    {
        $invoice = new InvoiceFinal();
        $paidDate = new \DateTimeImmutable('2024-02-10 14:30:00');

        $result = $invoice->setPaidAt($paidDate);

        $this->assertSame($invoice, $result);
        $this->assertSame($paidDate, $invoice->getPaidAt());
    }

    public function testCompleteInvoiceFinalDataFlow(): void
    {
        $invoice = new InvoiceFinal();
        $createdAt = new \DateTimeImmutable('2024-01-15 10:00:00');
        $dueDate = new \DateTimeImmutable('2024-02-15');
        $paidAt = new \DateTimeImmutable('2024-02-10');

        $file = new File();
        $file->setNameDisplay('FinalInvoice_2024-01.pdf');

        $invoice
            ->setSequentialNumber('2024-FIN-001')
            ->setReferenceNumber('FIN-REF-2024-00001')
            ->setPaymentReference('3521234567890123456789')
            ->setCreatedAt($createdAt)
            ->setDueDate($dueDate)
            ->setFile($file)
            ->setPaidAt($paidAt);

        $this->assertSame('2024-FIN-001', $invoice->getSequentialNumber());
        $this->assertSame('FIN-REF-2024-00001', $invoice->getReferenceNumber());
        $this->assertSame('3521234567890123456789', $invoice->getPaymentReference());
        $this->assertSame($createdAt, $invoice->getCreatedAt());
        $this->assertSame($dueDate, $invoice->getDueDate());
        $this->assertSame($file, $invoice->getFile());
        $this->assertSame($paidAt, $invoice->getPaidAt());
        $this->assertTrue($invoice->isPaid());
    }

    public function testSetPaidAtToNull(): void
    {
        $invoice = new InvoiceFinal();
        $paidDate = new \DateTimeImmutable('2024-02-10');

        $invoice->setPaidAt($paidDate);
        $this->assertTrue($invoice->isPaid());

        $result = $invoice->setPaidAt(null);

        $this->assertSame($invoice, $result);
        $this->assertNull($invoice->getPaidAt());
        $this->assertFalse($invoice->isPaid());
    }

    public function testTogglePaidStatus(): void
    {
        $invoice = new InvoiceFinal();

        $this->assertFalse($invoice->isPaid());

        $invoice->setPaid(true);
        $this->assertTrue($invoice->isPaid());

        $invoice->setPaid(false);
        $this->assertFalse($invoice->isPaid());

        $invoice->setPaid(true);
        $this->assertTrue($invoice->isPaid());
    }
}
