<?php
namespace Psys\OrderInvoiceBundle\Model\Order;


enum State :int
{
    case UNPAID = 1;
    case PAID = 2;
    case PARTIALLY_PAID = 3;
}
?>