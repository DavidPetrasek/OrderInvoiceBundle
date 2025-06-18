<?php
namespace Psys\OrderInvoiceBundle\Model\Invoice;


enum InvoiceType :int
{
    case PROFORMA = 1;
    case FINAL = 2;
}
?>