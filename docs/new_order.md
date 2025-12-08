[Back to index](../README.md)

Creating a new order and its proforma invoice
=============================================
A new order can be saved without an invoice; the invoice can be added later.

``` php
use Psys\OrderInvoiceBundle\Entity\Invoice;
use Psys\OrderInvoiceBundle\Entity\InvoiceBuyer;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceSeller;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Entity\OrderItem;
use Psys\OrderInvoiceBundle\Model\OrderItem\AmountType;
use Psys\OrderInvoiceBundle\Model\Order\PaymentMode;
use Psys\OrderInvoiceBundle\Model\Order\State;
use Psys\OrderInvoiceBundle\Service\InvoiceManager\InvoiceManager;
use Psys\OrderInvoiceBundle\Service\OrderManager\OrderManager;
use Symfony\Bundle\SecurityBundle\Security;
use App\Model\MyOrderCategory;


public function newOrder(OrderManager $orderManager, InvoiceManager $invoiceManager, Security $security) : void
{       
    $ent_Order = (new Order())
        ->setCategory(MyOrderCategory::SECOND_CATEGORY)
        ->setPaymentMode(PaymentMode::BANK_ACCOUNT_REGULAR)
        ->setPaymentModeBankAccount('5465878565/6556')
        ->setCustomer($security->getUser()) // Optional
        ->setCreatedAt(new \DateTimeImmutable())
        ->setState(State::NEW)
        ->setCurrency('USD');

    $ent_Order->addOrderItem(
        (new OrderItem())
            ->setName('Foo')
            ->setPriceVatIncluded(1599) // If not set, it will be automatically calculated from price exclusive of VAT
            ->setPriceVatExcluded(1300) // If not set, it will be automatically calculated from price inclusive of VAT
            ->setVatRate(21)
            ->setAmount(1)
            ->setAmountType(AmountType::ITEM)
    );

    $ent_InvoiceProforma = (new InvoiceProforma())
        ->setCreatedAt(new \DateTimeImmutable())
        ->setDueDate(new \DateTimeImmutable('+14 days'));
    
    $invoiceManager->setSequentialNumber($ent_InvoiceProforma);
    $ent_InvoiceProforma->setReferenceNumber(date('Y').$ent_InvoiceProforma->getSequentialNumber());

    $ent_Invoice = (new Invoice())
        ->setInvoiceProforma($ent_InvoiceProforma)
        ->setInvoiceBuyer
        (
            (new InvoiceBuyer())
            ->setFullName('John Doe')
            ->setStreetAddress1('Street')
            ->setStreetAddress2('123')
            ->setCity('Some City')
            ->setPostcode('25689')
            ->setRegion('Some Region')
            ->setCountry('Italy')
        )
        ->setInvoiceSeller
        (
            (new InvoiceSeller())
            ->setOrganization('Seller Organization')
            ->setStreetAddress1('Street 123')
            ->setStreetAddress2('123')
            ->setCity('Some City')
            ->setPostcode('25689')
            ->setRegion('Some Region')
            ->setCountry('United Kingdom')
            ->setVatIdentificationNumber('5468484')
            ->setCompanyIdentificationNumber('5655')
            ->setLegalEntityRegistrationDetails('Registered in England & Wales No. 01234567  ·  Registered office : 1 King’s Road, London SW1')
        );

    $invoiceManager->setUniqueVariableSymbol($ent_Invoice, length: 9);
    $ent_Order->setInvoice($ent_Invoice);
    $orderManager->processAndSaveNewOrder($ent_Order);
}
```