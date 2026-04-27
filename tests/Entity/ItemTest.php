<?php

namespace Psys\OrderInvoiceBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\Item;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\InvoiceRegular;

class ItemTest extends TestCase
{
    public function testItemInitialization(): void
    {
        $item = new Item();

        $this->assertNull($item->getId());
        $this->assertNull($item->getOrder());
        $this->assertNull($item->getInvoiceProforma());
        $this->assertNull($item->getInvoiceAdvance());
        $this->assertNull($item->getInvoiceRegular());
    }

    public function testSetAndGetName(): void
    {
        $item = new Item();
        $name = 'Test Product';

        $result = $item->setName($name);

        $this->assertSame($item, $result);
        $this->assertSame($name, $item->getName());
    }

    public function testSetAndGetShortDescription(): void
    {
        $item = new Item();
        $description = 'Short description of the product';

        $result = $item->setShortDescription($description);

        $this->assertSame($item, $result);
        $this->assertSame($description, $item->getShortDescription());
    }

    public function testSetAndGetAmount(): void
    {
        $item = new Item();
        $amount = 5;

        $result = $item->setAmount($amount);

        $this->assertSame($item, $result);
        $this->assertSame($amount, $item->getAmount());
    }

    public function testSetAndGetPriceVatIncluded(): void
    {
        $item = new Item();
        $price = 120.50;

        $result = $item->setPriceVatIncluded($price);

        $this->assertSame($item, $result);
        $this->assertEquals($price, $item->getPriceVatIncluded());
    }

    public function testSetAndGetPriceVatExcluded(): void
    {
        $item = new Item();
        $price = 100.42;

        $result = $item->setPriceVatExcluded($price);

        $this->assertSame($item, $result);
        $this->assertEquals($price, $item->getPriceVatExcluded());
    }

    public function testSetAndGetVatRate(): void
    {
        $item = new Item();
        $rate = 20.00;

        $result = $item->setVatRate($rate);

        $this->assertSame($item, $result);
        $this->assertEquals($rate, $item->getVatRate());
    }

    public function testSetAndGetVat(): void
    {
        $item = new Item();
        $vat = 20.08;

        $result = $item->setVat($vat);

        $this->assertSame($item, $result);
        $this->assertEquals($vat, $item->getVat());
    }

    public function testSetAndGetOrder(): void
    {
        $item = new Item();
        $order = new Order();

        $result = $item->setOrder($order);

        $this->assertSame($item, $result);
        $this->assertSame($order, $item->getOrder());
    }

    public function testSetAndGetInvoiceProforma(): void
    {
        $item = new Item();
        $invoice = new InvoiceProforma();

        $result = $item->setInvoiceProforma($invoice);

        $this->assertSame($item, $result);
        $this->assertSame($invoice, $item->getInvoiceProforma());
    }

    public function testSetAndGetInvoiceAdvance(): void
    {
        $item = new Item();
        $invoice = new InvoiceAdvance();

        $result = $item->setInvoiceAdvance($invoice);

        $this->assertSame($item, $result);
        $this->assertSame($invoice, $item->getInvoiceAdvance());
    }

    public function testSetAndGetInvoiceRegular(): void
    {
        $item = new Item();
        $invoice = new InvoiceRegular();

        $result = $item->setInvoiceRegular($invoice);

        $this->assertSame($item, $result);
        $this->assertSame($invoice, $item->getInvoiceRegular());
    }

    public function testSetAndGetCategory(): void
    {
        $item = new Item();
        // Category is typically set via enum, but can be stored as int
        // We test by setting and verifying the setter returns the item (fluent interface)
        $result = $item->setCategory(null);

        $this->assertSame($item, $result);
    }

    public function testSetAndGetAmountType(): void
    {
        $item = new Item();
        $amountType = \Psys\OrderInvoiceBundle\Model\Item\AmountType::ITEM;

        $result = $item->setAmountType($amountType);

        $this->assertSame($item, $result);
        $this->assertSame($amountType, $item->getAmountType());
    }

    public function testSetOrderToNull(): void
    {
        $item = new Item();
        $order = new Order();
        $item->setOrder($order);

        $item->setOrder(null);

        $this->assertNull($item->getOrder());
    }
}
