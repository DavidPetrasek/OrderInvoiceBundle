<?php
namespace Psys\OrderInvoiceBundle\Twig;

use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Service\OrderManager\OrderManager;
use Twig\Extension\RuntimeExtensionInterface;


class OrderRuntime implements RuntimeExtensionInterface
{
    public function __construct
    (
        readonly private OrderManager $orderManager
    )
    {}

    public function getInvoicesAdvanceTotals(Order $ent_Order): array
    {
        return $this->orderManager->getInvoicesAdvanceTotals($ent_Order);
    }
}