<?php
namespace Psys\OrderInvoiceBundle\Model\Order;


enum State :int
{
    case NEW = 1;
    case PAID = 2;
}
?>