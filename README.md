[![Packagist Downloads](https://img.shields.io/packagist/dm/psys/order-invoice-bundle?style=flat)](https://packagist.org/packages/psys/order-invoice-bundle)


# OrderInvoiceBundle : A Symfony Bundle
## Use case
- You're not running a typical online store — full-featured e-commerce platform would be overkill.
## Features
- manages orders and associated invoices
- generates invoices in PDF format

## Installation

`composer req psys/order-invoice-bundle`

Finish installation: `symfony console oib:configure`

Revert initial configuration: `symfony console oib:unconfigure` (UPCOMING FEATURE)


### Optional
`symfony console make:oib:category` - Creates enum to specify custom categories for orders or order items


## Usage

### Creating a new order and its proforma invoice
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
        ->setCategory(MyOrderCategory::SECOND_CATEGORY)
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

### PDF generation
Available generators: MpdfGenerator

Example using the MpdfGenerator:

``` php
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use Psys\OrderInvoiceBundle\Service\InvoiceGenerator\MpdfGenerator;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;
use App\Entity\MyFileEntity;

public function generateProformaInvoicePdf (MpdfGenerator $mpdfGenerator, Filesystem $filesystem, Order $order, EntityManagerInterface $entityManager, Environment $twig) : void
{
    // Generate PDF and save it to disk
    $htmlPDF = $this->twig->render('invoice/oi_default.html.twig', 
        [
            'ent_Order'  => $order,
            'invoiceType'  => InvoiceType::PROFORMA->name,
        ]); 
    $binaryPDF = $mpdfGenerator->generate($htmlPDF);
    $pdfAbsPath = $filesystem->tempnam('/some/dir', '', '.pdf'); 
    $filesystem->appendToFile($pdfAbsPath, $binaryPDF);
    
    // Save generated PDF in databse
    $ent_File = (new MyFileEntity())
        ->setMimeType('application/pdf')
        ->setNameFileSystem(basename($pdfAbsPath))
        ->setNameDisplay('my_invoice.pdf')
        ->setCreatedAt();

    $ent_InvoiceProforma = $ent_Order->getInvoice()->getInvoiceProforma();
    $ent_InvoiceProforma->setFile($ent_File);
    
    $entityManager->persist($ent_File);
    $entityManager->persist($ent_InvoiceProforma);
    $entityManager->flush();
}
```


### Reseting sequential numbers
Either create a ready-to-use cron controller: `symfony console make:oib:cron_controller` 

or reset them by:
``` php
use Psys\OrderInvoiceBundle\Service\InvoiceManager\InvoiceManager;
...
$invoiceManager->resetSequentialNumbers();
```