<?php

namespace Psys\OrderInvoiceBundle\Tests\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
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
}
