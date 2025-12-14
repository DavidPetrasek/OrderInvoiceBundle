<?php
namespace Psys\OrderInvoiceBundle\Service\InvoiceBinaryProvider;

use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;

abstract class AbstractInvoiceBinaryProvider implements InvoiceBinaryProviderInterface
{
    public function getBinary(Order $order, InvoiceType $invoiceType): string
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function getProforma(Order $order): string
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function getFinal(Order $order): string
    {
        throw new \BadMethodCallException('Not implemented');
    }
}