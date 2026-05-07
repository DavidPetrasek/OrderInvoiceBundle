<?php
namespace Psys\OrderInvoiceBundle\EventSubscriber;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceRegular;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException;

class DoctrineSubscriber
{
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $em = $eventArgs->getObjectManager();
        $uow = $em->getUnitOfWork();

        $ent_Order_insert = null;
        $ent_Order_update = null;
        $ent_InvoiceRegular_insert = null;
        $ent_InvoiceProforma_insert = null;
        $ent_InvoiceAdvance_insert = null;
        $ent_InvoiceFinal_insert = null;

        foreach ($uow->getScheduledEntityInsertions() as $entInsert) 
        {
            /** @var Order $ent_Order_insert  */
            if ($entInsert instanceof Order) {$ent_Order_insert = $entInsert;}
            
            /** @var InvoiceRegular $ent_InvoiceRegular_insert  */
            if ($entInsert instanceof InvoiceRegular) {$ent_InvoiceRegular_insert = $entInsert;}

            /** @var InvoiceProforma $ent_InvoiceProforma_insert  */
            if ($entInsert instanceof InvoiceProforma) {$ent_InvoiceProforma_insert = $entInsert;}
            
            /** @var InvoiceAdvance $ent_InvoiceAdvance_insert  */
            if ($entInsert instanceof InvoiceAdvance) {$ent_InvoiceAdvance_insert = $entInsert;}
            
            /** @var InvoiceFinal $ent_InvoiceFinal_insert  */
            if ($entInsert instanceof InvoiceFinal) {$ent_InvoiceFinal_insert = $entInsert;}
        }

        foreach ($uow->getScheduledEntityUpdates() as $entUpdate) 
        {
            /** @var Order $ent_Order_update  */
            if ($entUpdate instanceof Order) {$ent_Order_update = $entUpdate;}
        }

        if ($ent_Order_insert) // When creating a new order
        {
            $this->checkInvoice($ent_Order_insert);
        }
        
        if ($ent_Order_update) // Editing existing order
        {
            $this->checkInvoice($ent_Order_update);
        }

        // If the final invoice is just being issued
        if ($ent_InvoiceFinal_insert) 
        {
            // and proforma or advance invoice exists
            if ($ent_Order_update && (!$ent_Order_update->getInvoicesAdvance()->isEmpty() || $ent_Order_update->getInvoiceProforma()))
            {
                // and order has no items
                if ($ent_Order_update->getItems()->isEmpty())
                {
                    throw new InvalidInvoiceStateException('Final invoice requires the order to have at least one item (which represents the total price).');
                }
            }
        }

        // Simultaneous checks
        if ($ent_InvoiceProforma_insert && $ent_InvoiceAdvance_insert)
        {
            throw new InvalidInvoiceStateException('Proforma and advance invoice cannot be issued simultaneously.');
        }

        if ($ent_InvoiceProforma_insert && $ent_InvoiceRegular_insert)
        {
            throw new InvalidInvoiceStateException('Proforma and regular invoice cannot be issued simultaneously.');
        }

        if ($ent_InvoiceAdvance_insert && $ent_InvoiceRegular_insert)
        {
            throw new InvalidInvoiceStateException('Advance and regular invoice cannot be issued simultaneously.');
        }

        if ($ent_InvoiceFinal_insert && $ent_InvoiceProforma_insert)
        {
            throw new InvalidInvoiceStateException('Final and proforma invoice cannot be issued simultaneously.');
        }
    }

    private function checkInvoice(Order $ent_Order): void
    {
        $ent_InvoiceProforma = $ent_Order->getInvoiceProforma();
        $ent_InvoicesAdvance = $ent_Order->getInvoicesAdvance();
        $ent_InvoiceFinal = $ent_Order->getInvoiceFinal();

        if (empty($ent_InvoiceProforma) && $ent_InvoicesAdvance->isEmpty() && !empty($ent_InvoiceFinal)) 
        {
            throw new InvalidInvoiceStateException('Final invoice requires proforma or advance invoice to be issued first.');
        }
    }
 
}