<?php

declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\InvoiceRegular;
use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;
use Psys\OrderInvoiceBundle\Service\InvoiceManager\InvoiceManager;
use PHPUnit\Framework\TestCase;

class InvoiceManagerTest extends TestCase
{
    public function testSetSequentialNumberForProforma(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->exactly(3))
            ->method('executeStatement')
            ->willReturn(1);

        $result = $this->createMock(Result::class);
        $result->method('fetchOne')->willReturn('777');
        $connection->method('executeQuery')->willReturn($result);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);

        $manager = new InvoiceManager($em);

        $proforma = new InvoiceProforma();

        $manager->setSequentialNumber($proforma);

        $this->assertSame('777', $proforma->getSequentialNumber());
    }

    public function testSetSequentialNumberForAdvance(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->exactly(3))
            ->method('executeStatement')
            ->willReturn(1);

        $result = $this->createMock(Result::class);
        $result->method('fetchOne')->willReturn('888');
        $connection->method('executeQuery')->willReturn($result);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);

        $manager = new InvoiceManager($em);

        $advance = new InvoiceAdvance();

        $manager->setSequentialNumber($advance);

        $this->assertSame('888', $advance->getSequentialNumber());
    }

    public function testSetSequentialNumberForRegular(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->exactly(3))
            ->method('executeStatement')
            ->willReturn(1);

        $result = $this->createMock(Result::class);
        $result->method('fetchOne')->willReturn('999');
        $connection->method('executeQuery')->willReturn($result);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);

        $manager = new InvoiceManager($em);

        $regular = new InvoiceRegular();

        $manager->setSequentialNumber($regular);

        $this->assertSame('999', $regular->getSequentialNumber());
    }

    public function testSetSequentialNumberForFinal(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->exactly(3))
            ->method('executeStatement')
            ->willReturn(1);

        $result = $this->createMock(Result::class);
        $result->method('fetchOne')->willReturn('111');
        $connection->method('executeQuery')->willReturn($result);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);

        $manager = new InvoiceManager($em);

        $final = new InvoiceFinal();

        $manager->setSequentialNumber($final);

        $this->assertSame('111', $final->getSequentialNumber());
    }

    public function testSetSequentialNumberWithLargeNumber(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->exactly(3))
            ->method('executeStatement')
            ->willReturn(1);

        $result = $this->createMock(Result::class);
        $result->method('fetchOne')->willReturn('999999');
        $connection->method('executeQuery')->willReturn($result);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);

        $manager = new InvoiceManager($em);

        $proforma = new InvoiceProforma();

        $manager->setSequentialNumber($proforma);

        $this->assertSame('999999', $proforma->getSequentialNumber());
    }

    public function testSetSequentialNumberMultipleTimes(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->exactly(6))
            ->method('executeStatement')
            ->willReturn(1);

        $resultFirst = $this->createMock(Result::class);
        $resultFirst->method('fetchOne')->willReturn('777');

        $resultSecond = $this->createMock(Result::class);
        $resultSecond->method('fetchOne')->willReturn('888');

        $connection->method('executeQuery')
            ->willReturnOnConsecutiveCalls($resultFirst, $resultSecond);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);

        $manager = new InvoiceManager($em);

        $proforma1 = new InvoiceProforma();
        $proforma2 = new InvoiceProforma();

        $manager->setSequentialNumber($proforma1);
        $manager->setSequentialNumber($proforma2);

        $this->assertSame('777', $proforma1->getSequentialNumber());
        $this->assertSame('888', $proforma2->getSequentialNumber());
    }

    public function testSetUniquePaymentReferenceThrowsWhenInvoiceDoesNotExist(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->never())
            ->method('executeStatement');

        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn(false);
        $connection->method('executeQuery')->willReturn($result);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);

        $manager = new InvoiceManager($em);

        $invoice = new InvoiceProforma();
        $idProperty = new \ReflectionProperty(InvoiceProforma::class, 'id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($invoice, 1);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unique payment reference number cannot be set, because proforma invoice does not exist in the database. Make sure order was saved first.');

        $manager->setUniquePaymentReference($invoice);
    }

    public function testSetUniquePaymentReferenceRetriesWhenDuplicateIsFound(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->exactly(3))
            ->method('executeStatement')
            ->willReturn(1);

        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturn([1]);
        $connection->method('executeQuery')->willReturn($result);

        $duplicateQuery = $this->createMock(NativeQuery::class);
        $duplicateQuery->method('setParameter')->willReturnSelf();
        $duplicateQuery->method('getResult')->willReturn([['payment_reference' => '1234567890']]);

        $uniqueQuery = $this->createMock(NativeQuery::class);
        $uniqueQuery->method('setParameter')->willReturnSelf();
        $uniqueQuery->method('getResult')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);
        $em->method('createNativeQuery')->willReturnOnConsecutiveCalls($duplicateQuery, $uniqueQuery);

        $manager = new InvoiceManager($em);

        $invoice = new InvoiceProforma();
        $idProperty = new \ReflectionProperty(InvoiceProforma::class, 'id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($invoice, 1);

        $manager->setUniquePaymentReference($invoice, 10);

        $this->assertNotNull($invoice->getPaymentReference());
        $this->assertSame(10, strlen($invoice->getPaymentReference()));
    }

    public function testResetSequentialNumbersExecutesExpectedStatements(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->exactly(3))
            ->method('executeStatement')
            ->willReturn(1);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);

        $manager = new InvoiceManager($em);

        $manager->resetSequentialNumbers();
    }
}
