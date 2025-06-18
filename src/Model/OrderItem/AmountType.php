<?php
namespace Psys\OrderInvoiceBundle\Model\OrderItem;


enum AmountType :int
{
    case ITEM = 1;
    case HOUR = 2;
    case KILOGRAM = 3;
}
?>