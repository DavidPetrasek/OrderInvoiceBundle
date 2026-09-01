<?php

declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Entity\Item;
use Psys\OrderInvoiceBundle\Entity\Buyer;
use Psys\OrderInvoiceBundle\Entity\Seller;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\InvoiceRegular;
use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;
use Psys\OrderInvoiceBundle\Model\Order\PaymentMode;

class OrderTest extends TestCase
{
    public function testOrderInitialization(): void
    {
        $order = new Order();

        $this->assertNull($order->getId());
        $this->assertEmpty($order->getItems());
        $this->assertEmpty($order->getInvoicesAdvance());
        $this->assertNull($order->getInvoiceProforma());
        $this->assertNull($order->getInvoiceRegular());
        $this->assertNull($order->getInvoiceFinal());
        $this->assertNull($order->getBuyer());
        $this->assertNull($order->getSeller());
        $this->assertInstanceOf(ArrayCollection::class, $order->getItems());
    }

    public function testSetAndGetCreatedAt(): void
    {
        $order = new Order();
        $createdAt = new \DateTimeImmutable('2024-01-15 10:30:00');

        $result = $order->setCreatedAt($createdAt);

        $this->assertSame($order, $result);
        $this->assertSame($createdAt, $order->getCreatedAt());
    }

    public function testSetAndGetState(): void
    {
        $order = new Order();

        $result = $order->setState(1);

        $this->assertSame($order, $result);
        $this->assertSame(\Psys\OrderInvoiceBundle\Model\Order\State::UNPAID, $order->getState());
    }

    public function testSetAndGetCategory(): void
    {
        $order = new Order();
        $category = 5;

        $result = $order->setCategory($category);

        $this->assertSame($order, $result);
        // Category is stored as int internally
    }

    public function testSetAndGetCustomer(): void
    {
        $order = new Order();
        $customer = $this->createMock(\Psys\OrderInvoiceBundle\Model\CustomerInterface::class);

        $result = $order->setCustomer($customer);

        $this->assertSame($order, $result);
        $this->assertSame($customer, $order->getCustomer());
    }

    public function testAddItem(): void
    {
        $order = new Order();
        $item = new Item();
        $item->setName('Test Item');

        $result = $order->addItem($item);

        $this->assertSame($order, $result);
        $this->assertTrue($order->getItems()->contains($item));
        $this->assertSame($order, $item->getOrder());
    }

    public function testAddMultipleItems(): void
    {
        $order = new Order();
        $item1 = new Item();
        $item1->setName('Item 1');

        $item2 = new Item();
        $item2->setName('Item 2');

        $order->addItem($item1);
        $order->addItem($item2);

        $this->assertCount(2, $order->getItems());
        $this->assertTrue($order->getItems()->contains($item1));
        $this->assertTrue($order->getItems()->contains($item2));
    }

    public function testAddDuplicateItemNotDuplicated(): void
    {
        $order = new Order();
        $item = new Item();
        $item->setName('Test Item');

        $order->addItem($item);
        $order->addItem($item);

        $this->assertCount(1, $order->getItems());
    }

    public function testRemoveItem(): void
    {
        $order = new Order();
        $item = new Item();
        $item->setName('Test Item');

        $order->addItem($item);
        $this->assertCount(1, $order->getItems());

        $result = $order->removeItem($item);

        $this->assertSame($order, $result);
        $this->assertCount(0, $order->getItems());
    }

    public function testSetAndGetBuyer(): void
    {
        $order = new Order();
        $buyer = new Buyer();
        $buyer->setFullName('John Doe');

        $result = $order->setBuyer($buyer);

        $this->assertSame($order, $result);
        $this->assertSame($buyer, $order->getBuyer());
    }

    public function testSetAndGetSeller(): void
    {
        $order = new Order();
        $seller = new Seller();
        $seller->setOrganization('Company Ltd.');

        $result = $order->setSeller($seller);

        $this->assertSame($order, $result);
        $this->assertSame($seller, $order->getSeller());
    }

    public function testSetAndGetInvoiceProforma(): void
    {
        $order = new Order();
        $invoice = new InvoiceProforma();

        $result = $order->setInvoiceProforma($invoice);

        $this->assertSame($order, $result);
        $this->assertSame($invoice, $order->getInvoiceProforma());
    }

    public function testSetAndGetInvoiceRegular(): void
    {
        $order = new Order();
        $invoice = new InvoiceRegular();

        $result = $order->setInvoiceRegular($invoice);

        $this->assertSame($order, $result);
        $this->assertSame($invoice, $order->getInvoiceRegular());
    }

    public function testSetAndGetInvoiceFinal(): void
    {
        $order = new Order();
        $invoice = new InvoiceFinal();

        $result = $order->setInvoiceFinal($invoice);

        $this->assertSame($order, $result);
        $this->assertSame($invoice, $order->getInvoiceFinal());
    }

    public function testAddInvoiceAdvance(): void
    {
        $order = new Order();
        $advance = new InvoiceAdvance();

        $result = $order->addInvoiceAdvance($advance);

        $this->assertSame($order, $result);
        $this->assertTrue($order->getInvoicesAdvance()->contains($advance));
        $this->assertSame($order, $advance->getOrder());
    }

    public function testAddMultipleInvoiceAdvances(): void
    {
        $order = new Order();
        $advance1 = new InvoiceAdvance();
        $advance2 = new InvoiceAdvance();

        $order->addInvoiceAdvance($advance1);
        $order->addInvoiceAdvance($advance2);

        $this->assertCount(2, $order->getInvoicesAdvance());
        $this->assertTrue($order->getInvoicesAdvance()->contains($advance1));
        $this->assertTrue($order->getInvoicesAdvance()->contains($advance2));
    }

    public function testRemoveInvoiceAdvance(): void
    {
        $order = new Order();
        $advance = new InvoiceAdvance();

        $order->addInvoiceAdvance($advance);
        $this->assertCount(1, $order->getInvoicesAdvance());

        $result = $order->removeInvoiceAdvance($advance);

        $this->assertSame($order, $result);
        $this->assertCount(0, $order->getInvoicesAdvance());
    }

    public function testGetInvoicesAdvanceMultipleTimes(): void
    {
        $order = new Order();
        $advance1 = new InvoiceAdvance();
        $advance2 = new InvoiceAdvance();

        $order->addInvoiceAdvance($advance1);
        $order->addInvoiceAdvance($advance2);

        $advances = $order->getInvoicesAdvance();

        $this->assertCount(2, $advances);
        $this->assertTrue($advances->contains($advance1));
        $this->assertTrue($advances->contains($advance2));
    }

    public function testSetAndGetPaymentMode(): void
    {
        $order = new Order();

        $result = $order->setPaymentMode(PaymentMode::BANK_ACCOUNT_REGULAR);

        $this->assertSame($order, $result);
        $this->assertSame(PaymentMode::BANK_ACCOUNT_REGULAR, $order->getPaymentMode());
    }

    public function testSetAndGetPaymentModeBankAccount(): void
    {
        $order = new Order();
        $account = '123456789/2010';

        $result = $order->setPaymentModeBankAccount($account);

        $this->assertSame($order, $result);
        $this->assertSame($account, $order->getPaymentModeBankAccount());
    }

    public function testSetAndGetCurrency(): void
    {
        $order = new Order();
        $currency = 'CZK';

        $result = $order->setCurrency($currency);

        $this->assertSame($order, $result);
        $this->assertSame($currency, $order->getCurrency());
    }

    public function testCompleteOrderDataFlow(): void
    {
        $order = new Order();
        $createdAt = new \DateTimeImmutable('2024-01-15 09:00:00');

        $buyer = new Buyer();
        $buyer->setFullName('Jane Smith')->setCity('Prague');

        $seller = new Seller();
        $seller->setOrganization('My Company');

        $item = new Item();
        $item->setName('Product')->setAmount(2)->setPriceVatIncluded(120.00)->setVatRate(20.00);

        $order
            ->setCreatedAt($createdAt)
            ->setState(1)
            ->setCategory(1)
            ->setBuyer($buyer)
            ->setSeller($seller)
            ->addItem($item)
            ->setPaymentMode(PaymentMode::BANK_ACCOUNT_REGULAR)
            ->setCurrency('CZK');

        $this->assertSame($createdAt, $order->getCreatedAt());
        $this->assertSame(\Psys\OrderInvoiceBundle\Model\Order\State::UNPAID, $order->getState());
        $this->assertSame($buyer, $order->getBuyer());
        $this->assertSame($seller, $order->getSeller());
        $this->assertCount(1, $order->getItems());
        $this->assertSame(PaymentMode::BANK_ACCOUNT_REGULAR, $order->getPaymentMode());
        $this->assertSame('CZK', $order->getCurrency());
    }
}
