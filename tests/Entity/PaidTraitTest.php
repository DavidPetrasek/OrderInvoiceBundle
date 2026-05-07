<?php

namespace Psys\OrderInvoiceBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\InvoiceRegular;

class PaidTraitTest extends TestCase
{
    public function testPaidAtInitialization(): void
    {
        $invoice = new InvoiceRegular();

        $this->assertNull($invoice->getPaidAt());
        $this->assertFalse($invoice->isPaid());
    }

    public function testSetAndGetPaidAt(): void
    {
        $invoice = new InvoiceRegular();
        $paidDate = new \DateTimeImmutable('2024-02-10 14:30:00');

        $result = $invoice->setPaidAt($paidDate);

        $this->assertSame($invoice, $result);
        $this->assertSame($paidDate, $invoice->getPaidAt());
    }

    public function testSetPaidAtToNull(): void
    {
        $invoice = new InvoiceRegular();
        $paidDate = new \DateTimeImmutable('2024-02-10 14:30:00');

        $invoice->setPaidAt($paidDate);
        $result = $invoice->setPaidAt(null);

        $this->assertSame($invoice, $result);
        $this->assertNull($invoice->getPaidAt());
    }

    public function testSetPaidToTrue(): void
    {
        $invoice = new InvoiceRegular();
        $before = new \DateTimeImmutable();

        $result = $invoice->setPaid(true);

        $after = new \DateTimeImmutable();

        $this->assertSame($invoice, $result);
        $this->assertTrue($invoice->isPaid());
        $this->assertInstanceOf(\DateTimeImmutable::class, $invoice->getPaidAt());
        $this->assertGreaterThanOrEqual($before, $invoice->getPaidAt());
        $this->assertLessThanOrEqual($after, $invoice->getPaidAt());
    }

    public function testSetPaidToFalse(): void
    {
        $invoice = new InvoiceRegular();

        $invoice->setPaid(true);
        $this->assertTrue($invoice->isPaid());

        $result = $invoice->setPaid(false);

        $this->assertSame($invoice, $result);
        $this->assertFalse($invoice->isPaid());
        $this->assertNull($invoice->getPaidAt());
    }

    public function testIsPaidReturnsFalseInitially(): void
    {
        $invoice = new InvoiceRegular();

        $this->assertFalse($invoice->isPaid());
    }

    public function testIsPaidReturnsTrueWhenPaidAtIsSet(): void
    {
        $invoice = new InvoiceRegular();
        $paidDate = new \DateTimeImmutable('2024-02-10');

        $invoice->setPaidAt($paidDate);

        $this->assertTrue($invoice->isPaid());
    }

    public function testTogglePaidStatus(): void
    {
        $invoice = new InvoiceRegular();

        $this->assertFalse($invoice->isPaid());

        $invoice->setPaid(true);
        $this->assertTrue($invoice->isPaid());

        $invoice->setPaid(false);
        $this->assertFalse($invoice->isPaid());

        $invoice->setPaid(true);
        $this->assertTrue($invoice->isPaid());
    }

    public function testSetPaidTrueMultipleTimes(): void
    {
        $invoice = new InvoiceRegular();

        $invoice->setPaid(true);
        $firstPaidAt = $invoice->getPaidAt();

        $invoice->setPaid(true);
        $secondPaidAt = $invoice->getPaidAt();

        $this->assertNotSame($firstPaidAt, $secondPaidAt);
        $this->assertTrue($invoice->isPaid());
    }

    public function testPaidAtChangesWhenUsingSetPaid(): void
    {
        $invoice = new InvoiceRegular();
        $specificDate = new \DateTimeImmutable('2024-01-01 10:00:00');

        $invoice->setPaidAt($specificDate);
        $this->assertTrue($invoice->isPaid());
        $this->assertSame($specificDate, $invoice->getPaidAt());

        $invoice->setPaid(false);
        $this->assertNull($invoice->getPaidAt());
    }

    public function testChainedPaidOperations(): void
    {
        $invoice = new InvoiceRegular();
        $createdAt = new \DateTimeImmutable('2024-01-15 10:00:00');
        $paidAt = new \DateTimeImmutable('2024-02-10 14:30:00');

        $result = $invoice
            ->setCreatedAt($createdAt)
            ->setPaid(true)
            ->setPaidAt($paidAt);

        $this->assertSame($invoice, $result);
        $this->assertTrue($invoice->isPaid());
        $this->assertSame($paidAt, $invoice->getPaidAt());
    }
}
