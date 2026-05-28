[Back to index](index.md)

Creating a new order
====================

``` php
use Psys\OrderInvoiceBundle\Entity\Buyer;
use Psys\OrderInvoiceBundle\Entity\InvoiceRegular;
use Psys\OrderInvoiceBundle\Entity\Seller;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Entity\Item;
use Psys\OrderInvoiceBundle\Model\Item\AmountType;
use Psys\OrderInvoiceBundle\Model\Order\PaymentMode;
use Psys\OrderInvoiceBundle\Model\Order\State;
use Psys\OrderInvoiceBundle\Service\InvoiceManager\InvoiceManager;
use Psys\OrderInvoiceBundle\Service\OrderManager\OrderManager;
use Symfony\Bundle\SecurityBundle\Security;
use App\Model\MyOrderCategory;


public function newOrder(OrderManager $orderManager, InvoiceManager $invoiceManager, Security $security) : void
{       
    $order = (new Order())
        ->setCategory(MyOrderCategory::SECOND_CATEGORY) // Optional
        ->setCustomer($security->getUser()); // Optional

    $invoiceRegular = (new InvoiceRegular())
        ->setDueDate(new \DateTimeImmutable('+14 days')) // Optional
        ->setPaymentMode(PaymentMode::CREDIT_CARD)
        ->setCurrency('GBP')
        ->addItem((new Item())
            ->setName('Foo')
            ->setPriceVatIncluded(120) // If not set, it will be automatically calculated from price exclusive of VAT
            ->setPriceVatExcluded(100) // If not set, it will be automatically calculated from price inclusive of VAT
            ->setVatRate(20)
            ->setAmount(1)
            ->setAmountType(AmountType::ITEM)
    );
    
    $invoiceManager->setSequentialNumber($invoiceRegular);
    $invoiceRegular->setReferenceNumber(date('Y').$invoiceRegular->getSequentialNumber());

    $order
        ->setInvoiceRegular($invoiceRegular)
        ->setBuyer
        (
            (new Buyer())
            ->setFullName('John Buyer')
            ->setStreetAddress1('Street')
            ->setStreetAddress2('123')
            ->setCity('Dublin')
            ->setPostcode('12345')
            ->setRegion('Some Region')
            ->setCountry('Ireland')
        )
        ->setSeller
        (
            (new Seller())
            ->setOrganization('Seller Organization')
            ->setStreetAddress1('Street 123')
            ->setStreetAddress2('123')
            ->setCity('London')
            ->setPostcode('54321')
            ->setRegion('Some Region')
            ->setCountry('United Kingdom')
            ->setVatIdentificationNumber('5468484')
            ->setCompanyIdentificationNumber('5655')
            ->setLegalEntityRegistrationDetails('Registered in England & Wales No. 01234567  ·  Registered office : 1 King’s Road, London SW1')
        );

    $orderManager->save($order);
    $invoiceManager->setUniquePaymentReference($invoiceRegular, length: 9);
}
```

Editing existing order 
======================
- After edits are made to the `Order` (or its invoices, seller, ...), at the end just call:
``` php
    $orderManager->save($order);
```