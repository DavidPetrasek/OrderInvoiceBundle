<?php declare(strict_types=1);

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

        $order_insert = null;
        $order_update = null;
        $invoiceRegular_insert = null;
        $invoiceProforma_insert = null;
        $invoiceAdvance_insert = null;
        $invoiceFinal_insert = null;

        foreach ($uow->getScheduledEntityInsertions() as $entInsert) 
        {
            /** @var Order $order_insert  */
            if ($entInsert instanceof Order) {$order_insert = $entInsert;}
            
            /** @var InvoiceRegular $invoiceRegular_insert  */
            if ($entInsert instanceof InvoiceRegular) {$invoiceRegular_insert = $entInsert;}

            /** @var InvoiceProforma $invoiceProforma_insert  */
            if ($entInsert instanceof InvoiceProforma) {$invoiceProforma_insert = $entInsert;}
            
            /** @var InvoiceAdvance $invoiceAdvance_insert  */
            if ($entInsert instanceof InvoiceAdvance) {$invoiceAdvance_insert = $entInsert;}
            
            /** @var InvoiceFinal $invoiceFinal_insert  */
            if ($entInsert instanceof InvoiceFinal) {$invoiceFinal_insert = $entInsert;}
        }

        foreach ($uow->getScheduledEntityUpdates() as $entUpdate) 
        {
            /** @var Order $order_update  */
            if ($entUpdate instanceof Order) {$order_update = $entUpdate;}
        }

        if ($order_insert instanceof Order) // When creating a new order
        {
            $this->checkInvoice($order_insert);
        }
        
        if ($order_update instanceof Order) // Editing existing order
        {
            $this->checkInvoice($order_update);
        }

        // If the final invoice is just being issued
        if ($invoiceFinal_insert instanceof InvoiceFinal) 
        {
            // and proforma or advance invoice exists
            if ($order_update instanceof Order && (!$order_update->getInvoicesAdvance()->isEmpty() || $order_update->getInvoiceProforma() instanceof InvoiceProforma))
            {
                // and order has no items
                if ($order_update->getItems()->isEmpty())
                {
                    throw new InvalidInvoiceStateException('Final invoice requires the order to have at least one item (which represents the total price).');
                }
            }
        }

        // Simultaneous checks
        if ($invoiceProforma_insert instanceof InvoiceProforma && $invoiceAdvance_insert instanceof InvoiceAdvance)
        {
            throw new InvalidInvoiceStateException('Proforma and advance invoice cannot be issued simultaneously.');
        }

        if ($invoiceProforma_insert && $invoiceRegular_insert instanceof InvoiceRegular)
        {
            throw new InvalidInvoiceStateException('Proforma and regular invoice cannot be issued simultaneously.');
        }

        if ($invoiceAdvance_insert && $invoiceRegular_insert instanceof InvoiceRegular)
        {
            throw new InvalidInvoiceStateException('Advance and regular invoice cannot be issued simultaneously.');
        }

        if ($invoiceFinal_insert instanceof InvoiceFinal && $invoiceProforma_insert)
        {
            throw new InvalidInvoiceStateException('Final and proforma invoice cannot be issued simultaneously.');
        }
    }

    private function checkInvoice(Order $order): void
    {
        $invoiceProforma = $order->getInvoiceProforma();
        $invoicesAdvance = $order->getInvoicesAdvance();
        $invoiceFinal = $order->getInvoiceFinal();

        if (!$invoiceProforma instanceof InvoiceProforma && $invoicesAdvance->isEmpty() && $invoiceFinal instanceof InvoiceFinal) 
        {
            throw new InvalidInvoiceStateException('Final invoice requires proforma or advance invoice to be issued first.');
        }
    }
 
}
