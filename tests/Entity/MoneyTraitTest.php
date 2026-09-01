<?php

declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\Order\PaymentMode;

class MoneyTraitTest extends TestCase
{
    public function testMoneyTraitInitialization(): void
    {
        $order = new Order();

        $this->assertNull($order->getPaymentMode());
        $this->assertNull($order->getPaymentModeBankAccount());
        $this->assertEquals(0.0, $order->getPriceVatIncluded());
        $this->assertEquals(0.0, $order->getPriceVatExcluded());
        $this->assertEquals(0.0, $order->getPriceVatBase());
        $this->assertEquals(0.0, $order->getPriceVat());
        $this->assertNull($order->getCurrency());
    }

    public function testSetAndGetPaymentMode(): void
    {
        $order = new Order();

        $result = $order->setPaymentMode(PaymentMode::BANK_ACCOUNT_REGULAR);

        $this->assertSame($order, $result);
        $this->assertSame(PaymentMode::BANK_ACCOUNT_REGULAR, $order->getPaymentMode());
    }

    public function testSetPaymentModeWithInt(): void
    {
        $order = new Order();

        $result = $order->setPaymentMode(1);

        $this->assertSame($order, $result);
        $this->assertSame(PaymentMode::BANK_ACCOUNT_REGULAR, $order->getPaymentMode());
    }

    public function testSetPaymentModeToNull(): void
    {
        $order = new Order();
        $order->setPaymentMode(PaymentMode::BANK_ACCOUNT_REGULAR);

        $result = $order->setPaymentMode(null);

        $this->assertSame($order, $result);
        $this->assertNull($order->getPaymentMode());
    }

    public function testSetAndGetPaymentModeBankAccount(): void
    {
        $order = new Order();
        $account = '123456789/2010';

        $result = $order->setPaymentModeBankAccount($account);

        $this->assertSame($order, $result);
        $this->assertSame($account, $order->getPaymentModeBankAccount());
    }

    public function testSetPaymentModeBankAccountToNull(): void
    {
        $order = new Order();
        $order->setPaymentModeBankAccount('123456789/2010');

        $result = $order->setPaymentModeBankAccount(null);

        $this->assertSame($order, $result);
        $this->assertNull($order->getPaymentModeBankAccount());
    }

    public function testSetAndGetPriceVatIncluded(): void
    {
        $order = new Order();
        $price = 1200.50;

        $result = $order->setPriceVatIncluded($price);

        $this->assertSame($order, $result);
        $this->assertEquals($price, $order->getPriceVatIncluded());
    }

    public function testSetAndGetPriceVatExcluded(): void
    {
        $order = new Order();
        $price = 1000.42;

        $result = $order->setPriceVatExcluded($price);

        $this->assertSame($order, $result);
        $this->assertEquals($price, $order->getPriceVatExcluded());
    }

    public function testSetAndGetPriceVatBase(): void
    {
        $order = new Order();
        $price = 1000.00;

        $result = $order->setPriceVatBase($price);

        $this->assertSame($order, $result);
        $this->assertEquals($price, $order->getPriceVatBase());
    }

    public function testSetAndGetPriceVat(): void
    {
        $order = new Order();
        $price = 200.00;

        $result = $order->setPriceVat($price);

        $this->assertSame($order, $result);
        $this->assertEquals($price, $order->getPriceVat());
    }

    public function testSetAndGetCurrency(): void
    {
        $order = new Order();
        $currency = 'CZK';

        $result = $order->setCurrency($currency);

        $this->assertSame($order, $result);
        $this->assertSame($currency, $order->getCurrency());
    }

    public function testSetCurrencyToNull(): void
    {
        $order = new Order();
        $order->setCurrency('CZK');

        $result = $order->setCurrency(null);

        $this->assertSame($order, $result);
        $this->assertNull($order->getCurrency());
    }

    public function testMultipleCurrencies(): void
    {
        $order = new Order();

        $order->setCurrency('CZK');
        $this->assertSame('CZK', $order->getCurrency());

        $order->setCurrency('EUR');
        $this->assertSame('EUR', $order->getCurrency());

        $order->setCurrency('USD');
        $this->assertSame('USD', $order->getCurrency());
    }

    public function testCompleteMoneyTraitDataFlow(): void
    {
        $order = new Order();

        $order
            ->setPaymentMode(PaymentMode::BANK_ACCOUNT_REGULAR)
            ->setPaymentModeBankAccount('123456789/2010')
            ->setPriceVatIncluded(1200.00)
            ->setPriceVatExcluded(1000.00)
            ->setPriceVatBase(1000.00)
            ->setPriceVat(200.00)
            ->setCurrency('CZK');

        $this->assertSame(PaymentMode::BANK_ACCOUNT_REGULAR, $order->getPaymentMode());
        $this->assertSame('123456789/2010', $order->getPaymentModeBankAccount());
        $this->assertEquals(1200.00, $order->getPriceVatIncluded());
        $this->assertEquals(1000.00, $order->getPriceVatExcluded());
        $this->assertEquals(1000.00, $order->getPriceVatBase());
        $this->assertEquals(200.00, $order->getPriceVat());
        $this->assertSame('CZK', $order->getCurrency());
    }

    public function testZeroPrices(): void
    {
        $order = new Order();

        $order
            ->setPriceVatIncluded(0.00)
            ->setPriceVatExcluded(0.00)
            ->setPriceVatBase(0.00)
            ->setPriceVat(0.00);

        $this->assertEquals(0.00, $order->getPriceVatIncluded());
        $this->assertEquals(0.00, $order->getPriceVatExcluded());
        $this->assertEquals(0.00, $order->getPriceVatBase());
        $this->assertEquals(0.00, $order->getPriceVat());
    }

    public function testNegativePrices(): void
    {
        $order = new Order();

        $order
            ->setPriceVatIncluded(-1200.00)
            ->setPriceVatExcluded(-1000.00)
            ->setPriceVat(-200.00);

        $this->assertEquals(-1200.00, $order->getPriceVatIncluded());
        $this->assertEquals(-1000.00, $order->getPriceVatExcluded());
        $this->assertEquals(-200.00, $order->getPriceVat());
    }

    public function testDifferentPaymentModes(): void
    {
        $order1 = new Order();
        $order1->setPaymentMode(PaymentMode::BANK_ACCOUNT_REGULAR);
        $this->assertSame(PaymentMode::BANK_ACCOUNT_REGULAR, $order1->getPaymentMode());

        $order2 = new Order();
        $order2->setPaymentMode(PaymentMode::CREDIT_CARD);
        $this->assertSame(PaymentMode::CREDIT_CARD, $order2->getPaymentMode());
    }
}
