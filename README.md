[![Packagist Downloads](https://img.shields.io/packagist/dm/psys/order-invoice-bundle?style=flat)](https://packagist.org/packages/psys/order-invoice-bundle)


# OrderInvoiceBundle : A Symfony Bundle
## Use case
- You're not running a typical online store — full-featured e-commerce platforms would be overkill.
## Features
- manages orders and associated invoices
- exports invoices

## Installation

`composer req psys/order-invoice-bundle`

### 1. Set your target entities
``` yaml
# config/packages/doctrine.yaml
    orm:
        resolve_target_entities:                                                              
            Psys\OrderInvoiceBundle\Model\CustomerInterface: App\Entity\YourCustomerEntity
            Psys\OrderInvoiceBundle\Model\FileInterface: App\Entity\YourFileEntity
```
- And let them implement the interfaces mentioned


### 2. Init database

``` command
symfony console make:migration
```
Then rename the `migrations/VersionOrderInvoiceInit.php` (also the class inside), so it runs just after the migration you've just created.
``` command
symfony console doctrine:migrations:migrate
```

### 3. Define categories for orders and/or its items (OPTIONAL)

``` php
namespace App\Model;
use Psys\OrderInvoiceBundle\Model\Order\CategoryInterface;

enum MyOrderCategory :int implements CategoryInterface 
{
    case FOO = 1;
    case BAR = 2;
}
```


## How to use

### Creating a new order and its proforma invoice:
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


public function newOrder (OrderManager $orderManager, InvoiceManager $invoiceManager, Security $security) : void
{       
    $ent_Order = (new Order())
        ->setCategory(MyOrderCategory::FOO)
        ->setPaymentMode(PaymentMode::BANK_ACCOUNT_REGULAR)
        ->setPaymentModeBankAccount('5465878565/6556')
        ->setCustomer($security->getUser()) // Customer can be also null
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

    // Use custom formatting for the reference number
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

    $invoiceManager->setUniqueVariableSymbol($ent_Invoice);
    $ent_Order->setInvoice($ent_Invoice);
    $orderManager->processAndSaveNewOrder($ent_Order);
}
```

### Exporting an invoice:
Set the template path and choose the engine:
``` yaml
# config/packages/psys_order_invoice.yaml
psys_order_invoice:
    pdf_exporter:
        engine: mpdf
        template_path: invoice/oi_default.html.twig
```
**Available exporter engines:** mpdf

Or you can create your custom exporter by implementing the `Psys\OrderInvoiceBundle\Service\InvoiceExporter\ExporterInterface` and adjusting the config to:
``` yaml
# config/packages/psys_order_invoice.yaml
psys_order_invoice:
    pdf_exporter:
        class: App\Service\MyExporter
```

Example using the mpdf exporter:

``` php
use Psys\OrderInvoiceBundle\Service\InvoiceExporter\MpdfExporter;
use Psys\OrderInvoiceBundle\Model\Invoice\ExportMode;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;

public function saveInvoice (MpdfExporter $mpdfExporter, Filesystem $filesystem, Order $ent_Order, EntityManagerInterface $entityManager) : void
{
    $absPath = $filesystem->tempnam('/some/dir', '', '.pdf'); 
    
    $mpdfExporter->export($ent_Order, InvoiceType::PROFORMA, ExportMode::FILE, '', $absPath);
    
    $ent_File = (new File())
        ->setMimeType('application/pdf')
        ->setNameFileSystem(basename($absPath))
        ->setNameDisplay('my_invoice.pdf')
        ->setCreatedAt();

    $ent_InvoiceProforma = $ent_Order->getInvoice()->getInvoiceProforma();
    $ent_InvoiceProforma->setFile($ent_File);
    
    $entityManager->persist($ent_File);
    $entityManager->persist($ent_InvoiceProforma);
    $entityManager->flush();
}
```

### Reseting sequential numbers:
``` php
use App\Service\InvoiceManager;

public function resetSequentialNumbers (InvoiceManager $invoiceManager) : void
{       
    // This method is meant to be used inside a cron. 
    // This cron needs to be run 1 to 10 minutes before a new year.
    $invoiceManager->resetSequentialNumbersEveryYear();

    // Use this method for resetting sequential numbers whenever you want.
    $invoiceManager->resetSequentialNumbers();
}
```