<?php
namespace Psys\OrderInvoiceBundle\EventSubscriber;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Psys\OrderInvoiceBundle\Entity\Invoice;
use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;
use Psys\OrderInvoiceBundle\Exception\InvalidInvoiceStateException;

class DoctrineSubscriber
{
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $em = $eventArgs->getObjectManager();
        $uow = $em->getUnitOfWork();

        $ent_Invoice_insert = null;
        $ent_Invoice_update = null;
        $ent_InvoiceFinal_insert = null;

        foreach ($uow->getScheduledEntityInsertions() as $entInsert) 
        {
            /** @var Invoice $ent_Invoice_insert  */
            if ($entInsert instanceof Invoice) {$ent_Invoice_insert = $entInsert;}
            /** @var InvoiceFinal $ent_InvoiceFinal_insert  */
            if ($entInsert instanceof InvoiceFinal) {$ent_InvoiceFinal_insert = $entInsert;}
        }

        foreach ($uow->getScheduledEntityUpdates() as $entUpdate) 
        {
            /** @var Invoice $ent_Invoice_update  */
            if ($entUpdate instanceof Invoice) {$ent_Invoice_update = $entUpdate;}
        }

        if ($ent_Invoice_insert) // When creating a new order
        {
            $this->checkInvoice($ent_Invoice_insert);
        }
        
        if ($ent_Invoice_update) // Editing existing order
        {
            $this->checkInvoice($ent_Invoice_update);
        }

        // If the final invoice is just being issued
        if ($ent_InvoiceFinal_insert) 
        {
            // and at least one advance invoice exists
            if (!$ent_Invoice_update->getInvoicesAdvance()->isEmpty())
            {
                // and order has no items
                if ($ent_Invoice_update->getOrder()->getItems()->isEmpty())
                {
                    throw new InvalidInvoiceStateException('You need to add at least one item to the order (which represents the total value) from which advance invoices will be deducted.');
                }
            }
        }
    }

    private function checkInvoice(Invoice $ent_Invoice): void
    {
        $ent_InvoiceProforma = $ent_Invoice->getInvoiceProforma();
        $ent_InvoicesAdvance = $ent_Invoice->getInvoicesAdvance();
        $ent_InvoiceFinal = $ent_Invoice->getInvoiceFinal();

        if (empty($ent_InvoiceProforma) && $ent_InvoicesAdvance->isEmpty() && !empty($ent_InvoiceFinal)) 
        {
            throw new InvalidInvoiceStateException('Final invoice requires proforma or advance invoice to be issued first.');
        }

        if (!empty($ent_InvoiceProforma) && !$ent_InvoicesAdvance->isEmpty()) 
        {
            throw new InvalidInvoiceStateException('Proforma and advance invoice cannot be issued simultaneously.');
        }
    }
 
}