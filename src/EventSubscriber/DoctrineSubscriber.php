<?php
namespace Psys\OrderInvoiceBundle\EventSubscriber;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Psys\OrderInvoiceBundle\Entity\Invoice;


class DoctrineSubscriber
{
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $em = $eventArgs->getObjectManager();
        $uow = $em->getUnitOfWork();

        $ent_Invoice_insert = null;
        $ent_Invoice_update = null;

        foreach ($uow->getScheduledEntityInsertions() as $entInsert) 
        {
            /** @var Invoice $ent_Invoice_insert  */
            if ($entInsert instanceof Invoice) {$ent_Invoice_insert = $entInsert;}
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
    }

    private function checkInvoice(Invoice $ent_Invoice): void
    {
        $ent_InvoiceProforma = $ent_Invoice->getInvoiceProforma();
        $ent_InvoicesAdvance = $ent_Invoice->getInvoicesAdvance();
        $ent_InvoiceFinal = $ent_Invoice->getInvoiceFinal();

        if (empty($ent_InvoiceProforma) && $ent_InvoicesAdvance->isEmpty() && !empty($ent_InvoiceFinal)) 
        {
            throw new \RuntimeException('Final invoice requires proforma or advance invoice to be issued first.');
        }

        if (!empty($ent_InvoiceProforma) && !$ent_InvoicesAdvance->isEmpty()) 
        {
            throw new \RuntimeException('Proforma and advance invoice cannot be issued simultaneously.');
        }
    }
 
}