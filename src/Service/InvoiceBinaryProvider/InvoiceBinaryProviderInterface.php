<?php
namespace Psys\OrderInvoiceBundle\Service\InvoiceBinaryProvider;

use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;

interface InvoiceBinaryProviderInterface
{
    /**
     * Get binary content for any invoice type
     * 
     * @return string binary
     */
    public function getBinary(Order $order, InvoiceType $invoiceType): string;

    /**
     * Get proforma invoice binary content
     * 
     * @return string binary
     */
    public function getProforma(Order $order): string;

    /**
     * Get final invoice binary content
     * 
     * @return string binary
     */
    public function getFinal(Order $order): string;
}