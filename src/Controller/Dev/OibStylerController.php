<?php

namespace Psys\OrderInvoiceBundle\Controller\Dev;

use Psys\OrderInvoiceBundle\Entity\Invoice;
use Psys\OrderInvoiceBundle\Entity\InvoiceBuyer;
use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceSeller;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Entity\OrderItem;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;
use Psys\OrderInvoiceBundle\Model\Order\PaymentMode;
use Psys\OrderInvoiceBundle\Model\Order\State;
use Psys\OrderInvoiceBundle\Model\OrderItem\AmountType;
use Psys\OrderInvoiceBundle\Service\InvoiceBinaryProvider\InvoiceBinaryProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/_oib/styler')]
class OibStylerController extends AbstractController
{
    public function __construct
    (
        private readonly InvoiceBinaryProviderInterface $invoiceBinaryProvider,
    ) 
    {}

    #[Route('/{id}/{invoiceType}', methods: ['GET'])]
    public function index(?Order $ent_Order, string $invoiceType): Response
    {
        $invoiceType = InvoiceType::fromName($invoiceType);

        if (null === $ent_Order 
            || null === $ent_Order->getInvoice()->getInvoiceProforma() && $invoiceType === InvoiceType::PROFORMA
            || null === $ent_Order->getInvoice()->getInvoiceFinal() && $invoiceType === InvoiceType::FINAL
        ) 
        {
            $ent_Order = $this->getDummyOrder();
        }

        if ($invoiceType === InvoiceType::PROFORMA) 
        {
            $binary = $this->invoiceBinaryProvider->getProforma($ent_Order);
        }
        else if ($invoiceType === InvoiceType::FINAL)
        {
            $binary = $this->invoiceBinaryProvider->getFinal($ent_Order);
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
        $ent_Order = (new Order())
            ->setPaymentMode(PaymentMode::BANK_ACCOUNT_REGULAR)
            ->setPaymentModeBankAccount('5552228888/0600')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setState(State::PAID)
            ->setCurrency('GBP');

        $ent_Order->addOrderItem(
            (new OrderItem())
                ->setName('Foo')
                ->setPriceVatIncluded(240)
                ->setPriceVatExcluded(200)
                ->setVatRate(20)
                ->setVat(40)
                ->setAmount(2)
                ->setAmountType(AmountType::ITEM)
        );

        $ent_InvoiceProforma = (new InvoiceProforma())
            ->setCreatedAt(new \DateTimeImmutable())
            ->setDueDate(new \DateTimeImmutable('+14 days'))
            ->setSequentialNumber(8);
        $ent_InvoiceProforma->setReferenceNumber(date('Y').$ent_InvoiceProforma->getSequentialNumber());

        $ent_InvoiceFinal = (new InvoiceFinal())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setSequentialNumber(8);
        $ent_InvoiceFinal->setReferenceNumber(date('Y').$ent_InvoiceFinal->getSequentialNumber());

        $ent_Invoice = (new Invoice())
            ->setInvoiceProforma($ent_InvoiceProforma)
            ->setInvoiceFinal($ent_InvoiceFinal)
            ->setInvoiceBuyer
            (
                (new InvoiceBuyer())
                ->setFullName('John Buyer')
                ->setStreetAddress1('Street')
                ->setStreetAddress2('123')
                ->setCity('Dublin')
                ->setPostcode('12345')
                ->setRegion('Some Region')
                ->setCountry('Ireland')
            )
            ->setInvoiceSeller
            (
                (new InvoiceSeller())
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
            )
            ->setVariableSymbol('123456789');

        $ent_Order->setInvoice($ent_Invoice);

        $ent_Order->setPriceVatIncluded(240)
                  ->setPriceVatExcluded(200)
                  ->setPriceVatBase(200)
                  ->setPriceVat(40);
        
        return $ent_Order;
    }
}
