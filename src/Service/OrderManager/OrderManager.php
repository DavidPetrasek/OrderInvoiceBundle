<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Service\OrderManager;

use Psys\OrderInvoiceBundle\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException;
use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceRegular;
use Psys\OrderInvoiceBundle\Model\Order\State;
use Psys\OrderInvoiceBundle\Model\Order\PaymentMode;
use Psys\OrderInvoiceBundle\Service\Calculator;

class OrderManager
{    
    public function __construct
    (
        private readonly EntityManagerInterface $em,
        private readonly Calculator $calculator
    )
    {}    
    
    public function save(Order $order): void
    {
        $this->saveChecks($order);

        // Process order
        if (!$order->getItems()->isEmpty())
        {
            $orderTotals = $this->calculator->calculateTotals($order);
            $order->setPriceVatIncluded($orderTotals['vatIncluded'])
                    ->setPriceVatExcluded($orderTotals['vatExcluded'])
                    ->setPriceVatBase($orderTotals['vatBase'])
                    ->setPriceVat($orderTotals['vat']);
        }

        // Process proforma invoice
        $invoiceProforma = $order->getInvoiceProforma();
        if ($invoiceProforma instanceof InvoiceProforma)
        {
            $proformaTotals = $this->calculator->calculateTotals($invoiceProforma);
            $invoiceProforma->setPriceVatIncluded($proformaTotals['vatIncluded'])
                                ->setPriceVatExcluded($proformaTotals['vatExcluded'])
                                ->setPriceVatBase($proformaTotals['vatBase'])
                                ->setPriceVat($proformaTotals['vat']);
        }

        // Process regural invoice
        $invoiceRegular = $order->getInvoiceRegular();
        if ($invoiceRegular instanceof InvoiceRegular)
        {
            $regularTotals = $this->calculator->calculateTotals($invoiceRegular);
            $invoiceRegular->setPriceVatIncluded($regularTotals['vatIncluded'])
                            ->setPriceVatExcluded($regularTotals['vatExcluded'])
                            ->setPriceVatBase($regularTotals['vatBase'])
                            ->setPriceVat($regularTotals['vat']);

            if ($invoiceRegular->isPaid())
            {
                $order->setState(State::PAID);
            }
        }

        // Process final invoice
        $invoiceFinal = $order->getInvoiceFinal();
        
        if ($invoiceFinal instanceof InvoiceFinal)
        {
            // Calculate total amount due after deducting advance invoice payments
            $advancesTotals = $this->calculator->getInvoicesAdvanceTotals($order);
            $totalAmountDue = bcsub($order->getPriceVatIncluded(), $advancesTotals['vatIncluded'], 2);

            // Mark final invoice as paid if no amount remains due (amount due <= 0.00)
            if (bccomp($totalAmountDue, '0.00', 2) <= 0)
            {
                $invoiceFinal->setPaid(true);
            }
        }
        
        // Process advance invoices
        $allAdvancesWerePaid = true;

        foreach ($order->getInvoicesAdvance() as $invoiceAdvance) 
        {
            if (!$invoiceFinal && $invoiceAdvance->isPaid())
            {
                $order->setState(State::PARTIALLY_PAID);
            }
            else if ($invoiceFinal instanceof InvoiceFinal && !$invoiceAdvance->isPaid())
            {
                $allAdvancesWerePaid = false;
            }

            $invoiceAdvanceTotals = $this->calculator->calculateTotals($invoiceAdvance);

            $invoiceAdvance->setPriceVatIncluded($invoiceAdvanceTotals['vatIncluded'])
                    ->setPriceVatExcluded($invoiceAdvanceTotals['vatExcluded'])
                    ->setPriceVatBase($invoiceAdvanceTotals['vatBase'])
                    ->setPriceVat($invoiceAdvanceTotals['vat']);
        }

        if ($invoiceFinal instanceof InvoiceFinal && $invoiceFinal->isPaid() && $allAdvancesWerePaid)
        {
            $order->setState(State::PAID);
        }

        $this->em->persist($order);        
        $this->em->flush();
    }

    private function saveChecks(Order $order): void
    {
        $invoiceProforma = $order->getInvoiceProforma();
        if ($invoiceProforma instanceof InvoiceProforma)
        {
            if ($invoiceProforma->getItems()->isEmpty())
            {
                throw new InvalidInvoiceStateException('Proforma invoice has no items.');
            }

            if ($invoiceProforma->isPayable())
            {
                if (!$invoiceProforma->getPaymentMode() instanceof PaymentMode)
                {
                    throw new InvalidInvoiceStateException('Proforma invoice is payable and has no payment mode set.');
                }
            }
            else if ($invoiceProforma->isPaid())
            {
                throw new InvalidInvoiceStateException("Proforma invoice is not payable and therefore can't be marked as paid.");
            }

            if (empty($invoiceProforma->getCurrency()))
            {
                throw new InvalidInvoiceStateException('Proforma invoice has no currency set.');
            }
        }

        $invoiceFinal = $order->getInvoiceFinal();
        if (($invoiceProforma instanceof InvoiceProforma || !$order->getInvoicesAdvance()->isEmpty()) && $invoiceFinal instanceof InvoiceFinal)
        {
            if (!$order->getPaymentMode() instanceof PaymentMode)
            {
                throw new InvalidInvoiceStateException('Order has no payment mode set.');
            }
            if (empty($order->getCurrency()))
            {
                throw new InvalidInvoiceStateException('Order has no currency set.');
            }
        }

        $invoiceRegular = $order->getInvoiceRegular();
        if ($invoiceRegular instanceof InvoiceRegular)
        {
            if ($invoiceRegular->getItems()->isEmpty())
            {
                throw new InvalidInvoiceStateException('Regular invoice has no items.');
            }

            if (!$invoiceRegular->getPaymentMode() instanceof PaymentMode)
            {
                throw new InvalidInvoiceStateException('Regular invoice has no payment mode set.');
            }
            if (empty($invoiceRegular->getCurrency()))
            {
                throw new InvalidInvoiceStateException('Regular invoice has no currency set.');
            }
        }

        foreach ($order->getInvoicesAdvance() as $invoiceAdvance) 
        {
            if ($invoiceAdvance->getItems()->isEmpty())
            {
                throw new InvalidInvoiceStateException('Advance invoice has no items.');
            }
            if (empty($invoiceAdvance->getPaymentMode()))
            {
                throw new InvalidInvoiceStateException('Advance invoice has no payment mode set.');
            }
            if (empty($invoiceAdvance->getCurrency()))
            {
                throw new InvalidInvoiceStateException('Advance invoice has no currency set.');
            }
        }
    }
}
