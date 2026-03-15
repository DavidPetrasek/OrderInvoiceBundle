<?php
namespace Psys\OrderInvoiceBundle\Service\OrderManager;

use Psys\OrderInvoiceBundle\Entity\Order;

use Doctrine\ORM\EntityManagerInterface;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\Item;
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
        // Process order
        $orderTotals = $this->calculateTotals($ent_Order);

        $ent_Order->setPriceVatIncluded($orderTotals['vatIncluded'])
                  ->setPriceVatExcluded($orderTotals['vatExcluded'])
                  ->setPriceVatBase($orderTotals['vatBase'])
                  ->setPriceVat($orderTotals['vat']);

        // Process advance invoices
        foreach ($ent_Order->getInvoice()->getInvoicesAdvance() as $key => $ent_InvoiceAdvance) 
        {
            // Use order's values if nothing was set
            if (empty($ent_InvoiceAdvance->getPaymentMode()))
            {
                $ent_InvoiceAdvance->setPaymentMode($ent_Order->getPaymentMode());
            }
            if (empty($ent_InvoiceAdvance->getCurrency()))
            {
                $ent_InvoiceAdvance->setCurrency($ent_Order->getCurrency());
            }

            $invoiceAdvanceTotals = $this->calculateTotals($ent_InvoiceAdvance);

            $ent_InvoiceAdvance->setPriceVatIncluded($invoiceAdvanceTotals['vatIncluded'])
                    ->setPriceVatExcluded($invoiceAdvanceTotals['vatExcluded'])
                    ->setPriceVatBase($invoiceAdvanceTotals['vatBase'])
                    ->setPriceVat($invoiceAdvanceTotals['vat']);
        }

        $this->em->persist($ent_Order);        
        $this->em->flush();
    }
    
    public function calculateTotals(Order|InvoiceAdvance $ent_Order_Invoice): array
    {
        $priceVatExcludedTotal = 0;
        $priceVatIncludedTotal = 0;
        $vatBase = 0;
        
        foreach ($ent_Order_Invoice->getItems() as $item)
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