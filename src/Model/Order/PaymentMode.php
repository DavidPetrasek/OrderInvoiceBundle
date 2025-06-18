<?php
namespace Psys\OrderInvoiceBundle\Model\Order;


enum PaymentMode :int
{
    case BANK_ACCOUNT_REGULAR = 1;
    case BANK_ACCOUNT_ONLINE = 2;
    case CREDIT_CARD = 3;
}
?>