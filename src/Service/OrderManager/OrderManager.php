<?php
namespace Psys\OrderInvoiceBundle\Service\OrderManager;

use Psys\OrderInvoiceBundle\Entity\Order;

use Doctrine\ORM\EntityManagerInterface;
use Psys\OrderInvoiceBundle\Entity\OrderItem;
use Psys\Utils\Math;


class OrderManager
{    
    public function __construct
    (
        private readonly EntityManagerInterface $entityManager,
        private readonly Math $math
    )
    {}    
    
    public function processAndSaveNewOrder(Order $ent_Order): void
    {    
        $orderTotals = $this->calculateOrderTotals($ent_Order);

        $ent_Order->setPriceVatIncluded ($orderTotals['vatIncluded']);
        $ent_Order->setPriceVatExcluded ($orderTotals['vatExcluded']);
        $ent_Order->setPriceVatBase ($orderTotals['vatBase']);
        $ent_Order->setPriceVat ($orderTotals['vat']);
        
        $this->entityManager->persist($ent_Order);        
        $this->entityManager->flush();
    }
    
    public function calculateOrderTotals(Order $ent_Order): array
    {
        $priceVatExcludedTotal = 0;
        $priceVatIncludedTotal = 0;
        $vatBase = 0;
        
        foreach ($ent_Order->getOrderItems() as $orderItem)
        {
            $orderItemTotals = $this->calculateOrderItemTotals($orderItem);
            $amount = $orderItem->getAmount();

            if ($orderItem->getVatRate() > 0) {$vatBase += $orderItemTotals['priceVatExcluded'] * $amount;}

            $priceVatIncludedTotal += $orderItemTotals['priceVatIncluded'] * $amount;
            $priceVatExcludedTotal += $orderItemTotals['priceVatExcluded'] * $amount;
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

    private function calculateOrderItemTotals(OrderItem $orderItem) : array
    {
        $priceVatIncluded = $orderItem->getPriceVatIncluded();
        $priceVatExcluded = $orderItem->getPriceVatExcluded();

        // Calculate price exclusive of VAT from price inclusive of VAT
        if (!empty($priceVatIncluded)) 
        {            
            $priceVatExcluded = $this->math->subtractPercentage($priceVatIncluded, $orderItem->getVatRate());
            $orderItem->setPriceVatExcluded($priceVatExcluded);
        }

        // Calculate price inclusive of VAT from price exclusive of VAT
        else if (!empty($priceVatExcluded)) 
        {
            $priceVatIncluded = $this->math->addPercentage($priceVatExcluded, $orderItem->getVatRate());
            $orderItem->setPriceVatIncluded($priceVatIncluded);
        }
        
        $orderItem->setVat($priceVatIncluded - $priceVatExcluded);

        return 
        [
            'priceVatIncluded' => $priceVatIncluded,
            'priceVatExcluded' => $priceVatExcluded,
        ];
    }
}

?>