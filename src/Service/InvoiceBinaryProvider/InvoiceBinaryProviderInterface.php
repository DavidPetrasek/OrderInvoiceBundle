<?php
namespace Psys\OrderInvoiceBundle\Service\InvoiceBinaryProvider;

use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;

interface InvoiceBinaryProviderInterface
{
    /**
     * Get binary content for any invoice type
     * 
     * @return string binary
     */
    public function getBinary(Order $order, InvoiceType $invoiceType, ?InvoiceAdvance $ent_InvoiceAdvance = null): string;

    /**
     * Get regular invoice binary content
     * 
     * @return string binary
     */
    public function getRegular(Order $order): string;

    /**
     * Get proforma invoice binary content
     * 
     * @return string binary
     */
    public function getProforma(Order $order): string;

    /**
     * Get advance invoice binary content
     * 
     * @return string binary
     */
    public function getAdvance(InvoiceAdvance $ent_InvoiceAdvance): string;

    /**
     * Get final invoice binary content
     * 
     * @return string binary
     */
    public function getFinal(Order $order): string;
}