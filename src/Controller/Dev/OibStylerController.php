<?php

declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Controller\Dev;

use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\Buyer;
use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceRegular;
use Psys\OrderInvoiceBundle\Entity\Seller;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Entity\Item;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;
use Psys\OrderInvoiceBundle\Model\Order\PaymentMode;
use Psys\OrderInvoiceBundle\Model\Order\State;
use Psys\OrderInvoiceBundle\Model\Item\AmountType;
use Psys\OrderInvoiceBundle\Service\InvoiceBinaryProvider\InvoiceBinaryProviderInterface;
use Psys\OrderInvoiceBundle\Service\OrderManager\OrderManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/_oib/styler')]
class OibStylerController extends AbstractController
{
    public function __construct
    (
        private readonly InvoiceBinaryProviderInterface $invoiceBinaryProvider,
        private readonly OrderManager $orderManager,
    ) 
    {}

    #[Route('/{id}/{invoiceType}', methods: ['GET'])]
    public function index(?Order $order, string $invoiceType): Response
    {
        $invoiceType = InvoiceType::fromName($invoiceType);

        if (!$order instanceof Order 
            || !$order->getInvoiceProforma() instanceof InvoiceProforma && $invoiceType === InvoiceType::PROFORMA
            || !$order->getInvoiceFinal() instanceof InvoiceFinal && $invoiceType === InvoiceType::FINAL
            || $order->getInvoicesAdvance()->isEmpty() && $invoiceType === InvoiceType::ADVANCE
            || !$order->getInvoiceRegular() instanceof InvoiceRegular && $invoiceType === InvoiceType::REGULAR
        ) 
        {
            $order = $this->getDummyOrder();
        }

        if ($invoiceType === InvoiceType::PROFORMA) 
        {
            $binary = $this->invoiceBinaryProvider->getProforma($order);
        }
        else if ($invoiceType === InvoiceType::FINAL)
        {
            $binary = $this->invoiceBinaryProvider->getFinal($order);
        }
        else if ($invoiceType === InvoiceType::ADVANCE)
        {
            $binary = $this->invoiceBinaryProvider->getAdvance($order->getInvoicesAdvance()->first());
        }
        else if ($invoiceType === InvoiceType::REGULAR)
        {
            $binary = $this->invoiceBinaryProvider->getRegular($order);
        }

        return new Response(
            $binary,
            Response::HTTP_OK,
            [
                'Content-Type' => (new \finfo(FILEINFO_MIME_TYPE))->buffer($binary)
            ]
        );
    }

    private function getDummyOrder() : Order
    {       
        $order = (new Order())
            ->setPaymentMode(PaymentMode::BANK_ACCOUNT_REGULAR)
            ->setPaymentModeBankAccount('5552228888/0600')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setState(State::PAID)
            ->setCurrency('GBP')
            ->addItem((new Item())
                ->setName('Foundation works – concrete + labour')
                ->setPriceVatIncluded(84700)
                ->setVatRate(21)
                ->setAmount(1)
                ->setAmountType(AmountType::ITEM))
            ->addItem((new Item())
                ->setName('Structural works – walls, roof')
                ->setPriceVatIncluded(96800)
                ->setVatRate(21)
                ->setAmount(1)
                ->setAmountType(AmountType::ITEM))
            ->addItem((new Item())
                ->setName('Finishing – plumbing, electrics, plastering')
                ->setPriceVatIncluded(60500)
                ->setVatRate(21)
                ->setAmount(1)
                ->setAmountType(AmountType::ITEM));

        $invoiceProforma = (new InvoiceProforma())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setDueDate(new \DateTimeImmutable('+14 days'))
            ->setSequentialNumber(1)
            ->setPayable(true)
            ->setPaymentMode(PaymentMode::BANK_ACCOUNT_REGULAR)
            ->setPaymentModeBankAccount('5552228888/0600')
            ->setPaymentReference('123456789')
            ->setCurrency('GBP')
            ->addItem((new Item())
                ->setName('Deposit for construction works')
                ->setPriceVatIncluded(72600)
                ->setVatRate(21)
                ->setAmount(1)
                ->setAmountType(AmountType::ITEM));
        $invoiceProforma->setReferenceNumber(date('Y').$invoiceProforma->getSequentialNumber());

        $invoiceAdvance = (new InvoiceAdvance())
            ->setCreatedAt(new \DateTimeImmutable('+10 days'))
            ->setSequentialNumber(1)
            ->setCurrency('GBP')
            ->setPaymentMode(PaymentMode::BANK_ACCOUNT_REGULAR)
            ->setPaymentModeBankAccount('5552228888/0600')
            ->setPaymentReference('123456789');
        $invoiceAdvance->setReferenceNumber(date('Y').$invoiceAdvance->getSequentialNumber());
        $invoiceAdvance->addItem(
            (new Item())
                ->setName('Deposit 30% – construction of house')
                ->setPriceVatIncluded(72600)
                ->setVatRate(21)
                ->setAmount(1)
                ->setAmountType(AmountType::ITEM)
        );

        $invoiceAdvanceTwo = (new InvoiceAdvance())
            ->setCreatedAt(new \DateTimeImmutable('+60 days'))
            ->setDueDate(new \DateTimeImmutable('+74 days'))
            ->setSequentialNumber(2)
            ->setCurrency('GBP')
            ->setPaymentMode(PaymentMode::BANK_ACCOUNT_REGULAR)
            ->setPaymentModeBankAccount('5552228888/0600')
            ->setPaymentReference('123456789');
        $invoiceAdvanceTwo->setReferenceNumber(date('Y').$invoiceAdvanceTwo->getSequentialNumber());
        $invoiceAdvanceTwo->addItem(
            (new Item())
                ->setName('Payment for foundations – milestone 2')
                ->setPriceVatIncluded(48400)
                ->setVatRate(21)
                ->setAmount(1)
                ->setAmountType(AmountType::ITEM)
        );

        $invoiceFinal = (new InvoiceFinal())
            ->setCreatedAt(new \DateTimeImmutable('+130 days'))
            ->setDueDate(new \DateTimeImmutable('+144 days'))
            ->setSequentialNumber(1)
            ->setPaymentReference('123456789');
        $invoiceFinal->setReferenceNumber(date('Y').$invoiceFinal->getSequentialNumber());

        
        $invoiceRegular = (new InvoiceRegular())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setDueDate(new \DateTimeImmutable('+14 days'))
            ->setSequentialNumber(1)
            ->setPaymentMode(PaymentMode::CREDIT_CARD)
            ->setCurrency('GBP')
            ->setPaymentReference('777444663')
            ->addItem((new Item())
                ->setName('Shoes')
                ->setPriceVatIncluded(15)
                ->setVatRate(21)
                ->setAmount(1)
                ->setAmountType(AmountType::ITEM))
            ->addItem((new Item())
                ->setName('Socks')
                ->setPriceVatIncluded(4)
                ->setVatRate(21)
                ->setAmount(4)
                ->setAmountType(AmountType::ITEM));
        $invoiceRegular->setReferenceNumber(date('Y').$invoiceRegular->getSequentialNumber());

        
         $order
            ->setInvoiceProforma($invoiceProforma)
            ->setInvoiceFinal($invoiceFinal)
            ->addInvoiceAdvance($invoiceAdvance)
            ->addInvoiceAdvance($invoiceAdvanceTwo)
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
                ->setLegalEntityRegistrationDetails('Registered in England & Wales No. 01234567  ·  Registered office : 1 King’s Road, London SW1')
            );


        // Calculate
        $orderTotals = $this->orderManager->calculateTotals($order);
        $order->setPriceVatIncluded($orderTotals['vatIncluded'])
                  ->setPriceVatExcluded($orderTotals['vatExcluded'])
                  ->setPriceVatBase($orderTotals['vatBase'])
                  ->setPriceVat($orderTotals['vat']);
                
        $proformaTotals = $this->orderManager->calculateTotals($invoiceProforma);
        $invoiceProforma->setPriceVatIncluded($proformaTotals['vatIncluded'])
                            ->setPriceVatExcluded($proformaTotals['vatExcluded'])
                            ->setPriceVatBase($proformaTotals['vatBase'])
                            ->setPriceVat($proformaTotals['vat']);

        $regularTotals = $this->orderManager->calculateTotals($invoiceRegular);
        $invoiceRegular->setPriceVatIncluded($regularTotals['vatIncluded'])
                        ->setPriceVatExcluded($regularTotals['vatExcluded'])
                        ->setPriceVatBase($regularTotals['vatBase'])
                        ->setPriceVat($regularTotals['vat']);

        foreach ($order->getInvoicesAdvance() as $invoiceAdvance) 
        {
            $invoiceAdvanceTotals = $this->orderManager->calculateTotals($invoiceAdvance);

            $invoiceAdvance->setPriceVatIncluded($invoiceAdvanceTotals['vatIncluded'])
                    ->setPriceVatExcluded($invoiceAdvanceTotals['vatExcluded'])
                    ->setPriceVatBase($invoiceAdvanceTotals['vatBase'])
                    ->setPriceVat($invoiceAdvanceTotals['vat']);
        }

        return $order;
    }
}
