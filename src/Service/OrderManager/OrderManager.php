<?php
namespace Psys\OrderInvoiceBundle\Service\OrderManager;

use Psys\OrderInvoiceBundle\Entity\Order;

use Doctrine\ORM\EntityManagerInterface;
use Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceRegular;
use Psys\OrderInvoiceBundle\Entity\Item;
use Psys\OrderInvoiceBundle\Model\Order\State;
use Psys\Utils\Math;


class OrderManager
{    
    public function __construct
    (
        private readonly EntityManagerInterface $em,
        private readonly Math $math
    )
    {}    
    
    public function save(Order $ent_Order): void
    {
        $this->saveChecks($ent_Order);

        // Process order
        if (!$ent_Order->getItems()->isEmpty())
        {
            $orderTotals = $this->calculateTotals($ent_Order);
            $ent_Order->setPriceVatIncluded($orderTotals['vatIncluded'])
                    ->setPriceVatExcluded($orderTotals['vatExcluded'])
                    ->setPriceVatBase($orderTotals['vatBase'])
                    ->setPriceVat($orderTotals['vat']);
        }

        // Process proforma invoice
        $ent_InvoiceProforma = $ent_Order->getInvoiceProforma();
        if ($ent_InvoiceProforma)
        {
            $proformaTotals = $this->calculateTotals($ent_InvoiceProforma);
            $ent_InvoiceProforma->setPriceVatIncluded($proformaTotals['vatIncluded'])
                                ->setPriceVatExcluded($proformaTotals['vatExcluded'])
                                ->setPriceVatBase($proformaTotals['vatBase'])
                                ->setPriceVat($proformaTotals['vat']);
        }

        // Process regural invoice
        $ent_InvoiceRegular = $ent_Order->getInvoiceRegular();
        if ($ent_InvoiceRegular)
        {
            $regularTotals = $this->calculateTotals($ent_InvoiceRegular);
            $ent_InvoiceRegular->setPriceVatIncluded($regularTotals['vatIncluded'])
                            ->setPriceVatExcluded($regularTotals['vatExcluded'])
                            ->setPriceVatBase($regularTotals['vatBase'])
                            ->setPriceVat($regularTotals['vat']);

            if ($ent_InvoiceRegular->isPaid())
            {
                $ent_Order->setState(State::PAID);
            }
        }

        // Process final invoice
        $ent_InvoiceFinal = $ent_Order->getInvoiceFinal();
        
        if ($ent_InvoiceFinal)
        {
            // Calculate total amount due after deducting advance invoice payments
            $advancesTotals = $this->getInvoicesAdvanceTotals($ent_Order);
            $totalAmountDue = $ent_Order->getPriceVatIncluded() - $advancesTotals['vatIncluded'];

            // Mark final invoice as paid if no amount remains due
            if (abs($totalAmountDue) < PHP_FLOAT_EPSILON)
            {
                $ent_InvoiceFinal->setPaid(true);
            }
        }
        
        // Process advance invoices
        $allAdvancesWerePaid = true;

        foreach ($ent_Order->getInvoicesAdvance() as $ent_InvoiceAdvance) 
        {
            if (!$ent_InvoiceFinal && $ent_InvoiceAdvance->isPaid())
            {
                $ent_Order->setState(State::PARTIALLY_PAID);
            }
            else if ($ent_InvoiceFinal && !$ent_InvoiceAdvance->isPaid())
            {
                $allAdvancesWerePaid = false;
            }

            $invoiceAdvanceTotals = $this->calculateTotals($ent_InvoiceAdvance);

            $ent_InvoiceAdvance->setPriceVatIncluded($invoiceAdvanceTotals['vatIncluded'])
                    ->setPriceVatExcluded($invoiceAdvanceTotals['vatExcluded'])
                    ->setPriceVatBase($invoiceAdvanceTotals['vatBase'])
                    ->setPriceVat($invoiceAdvanceTotals['vat']);
        }

        if ($ent_InvoiceFinal && $ent_InvoiceFinal->isPaid() && $allAdvancesWerePaid)
        {
            $ent_Order->setState(State::PAID);
        }

        $this->em->persist($ent_Order);        
        $this->em->flush();
    }

    private function saveChecks(Order $ent_Order): void
    {
        $ent_InvoiceProforma = $ent_Order->getInvoiceProforma();
        if ($ent_InvoiceProforma)
        {
            if ($ent_InvoiceProforma->getItems()->isEmpty())
            {
                throw new InvalidInvoiceStateException('Proforma invoice has no items.');
            }

            if ($ent_InvoiceProforma->isPayable())
            {
                if (empty($ent_InvoiceProforma->getPaymentMode()))
                {
                    throw new InvalidInvoiceStateException('Proforma invoice is payable and has no payment mode set.');
                }
            }
            else if ($ent_InvoiceProforma->isPaid())
            {
                throw new InvalidInvoiceStateException('Proforma invoice is not payable and therefore can\'t be marked as paid.');
            }

            if (empty($ent_InvoiceProforma->getCurrency()))
            {
                throw new InvalidInvoiceStateException('Proforma invoice has no currency set.');
            }
        }

        $ent_InvoiceFinal = $ent_Order->getInvoiceFinal();
        if (($ent_InvoiceProforma || !$ent_Order->getInvoicesAdvance()->isEmpty()) && $ent_InvoiceFinal)
        {
            if (empty($ent_Order->getPaymentMode()))
            {
                throw new InvalidInvoiceStateException('Order has no payment mode set.');
            }
            if (empty($ent_Order->getCurrency()))
            {
                throw new InvalidInvoiceStateException('Order has no currency set.');
            }
        }

        $ent_InvoiceRegular = $ent_Order->getInvoiceRegular();
        if ($ent_InvoiceRegular)
        {
            if ($ent_InvoiceRegular->getItems()->isEmpty())
            {
                throw new InvalidInvoiceStateException('Regular invoice has no items.');
            }

            if (empty($ent_InvoiceRegular->getPaymentMode()))
            {
                throw new InvalidInvoiceStateException('Regular invoice has no payment mode set.');
            }
            if (empty($ent_InvoiceRegular->getCurrency()))
            {
                throw new InvalidInvoiceStateException('Regular invoice has no currency set.');
            }
        }

        foreach ($ent_Order->getInvoicesAdvance() as $ent_InvoiceAdvance) 
        {
            if ($ent_InvoiceAdvance->getItems()->isEmpty())
            {
                throw new InvalidInvoiceStateException('Advance invoice has no items.');
            }
            if (empty($ent_InvoiceAdvance->getPaymentMode()))
            {
                throw new InvalidInvoiceStateException('Advance invoice has no payment mode set.');
            }
            if (empty($ent_InvoiceAdvance->getCurrency()))
            {
                throw new InvalidInvoiceStateException('Advance invoice has no currency set.');
            }
        }
    }

    /**
     * Adds up totals of all advance invoices
     */
    public function getInvoicesAdvanceTotals(Order $ent_Order): array
    {
        $advanceTotals = [
            'vatIncluded' => 0.0,
            'vatExcluded' => 0.0,
            'vatBase'     => 0.0,
            'vat'         => 0.0,
        ];

        foreach ($ent_Order->getInvoicesAdvance() as $ent_InvoiceAdvance) 
        {
            $advanceTotals['vatIncluded'] += $ent_InvoiceAdvance->getPriceVatIncluded();
            $advanceTotals['vatExcluded'] += $ent_InvoiceAdvance->getPriceVatExcluded();
            $advanceTotals['vatBase']     += $ent_InvoiceAdvance->getPriceVatBase();
            $advanceTotals['vat']         += $ent_InvoiceAdvance->getPriceVat();
        }

        return $advanceTotals;
    }
    
    public function calculateTotals(Order|InvoiceProforma|InvoiceAdvance|InvoiceRegular $ent): array
    {
        $priceVatExcludedTotal = 0;
        $priceVatIncludedTotal = 0;
        $vatBase = 0;
        
        foreach ($ent->getItems() as $item)
        {
            $itemTotals = $this->calculateItemTotals($item);
            $amount = $item->getAmount();

            if ($item->getVatRate() > 0) {$vatBase += $itemTotals['priceVatExcluded'] * $amount;}

            $priceVatIncludedTotal += $itemTotals['priceVatIncluded'] * $amount;
            $priceVatExcludedTotal += $itemTotals['priceVatExcluded'] * $amount;
        }
        
        $vatTotal = $priceVatIncludedTotal - $priceVatExcludedTotal;
        
        return
        [
            'vatIncluded' => $priceVatIncludedTotal,
            'vatExcluded' => $priceVatExcludedTotal,
            'vatBase' => $vatBase,
            'vat' => $vatTotal,
        ];
    }

    private function calculateItemTotals(Item $item) : array
    {
        $priceVatIncluded = $item->getPriceVatIncluded();
        $priceVatExcluded = $item->getPriceVatExcluded();

        // Calculate price exclusive of VAT from price inclusive of VAT
        if (!empty($priceVatIncluded)) 
        {            
            $priceVatExcluded = $this->math->subtractPercentage($priceVatIncluded, $item->getVatRate());
            $item->setPriceVatExcluded($priceVatExcluded);
        }

        // Calculate price inclusive of VAT from price exclusive of VAT
        else if (!empty($priceVatExcluded)) 
        {
            $priceVatIncluded = $this->math->addPercentage($priceVatExcluded, $item->getVatRate());
            $item->setPriceVatIncluded($priceVatIncluded);
        }
        
        $item->setVat($priceVatIncluded - $priceVatExcluded);

        return 
        [
            'priceVatIncluded' => $priceVatIncluded,
            'priceVatExcluded' => $priceVatExcluded,
        ];
    }
}

?>