<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Twig\Extension;

use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Service\Calculator;
use Twig\Attribute\AsTwigFilter;

class InvoiceExtension
{
    public function __construct
    (
        readonly private Calculator $calculator
    )
    {}

    #[AsTwigFilter('invoices_advance_totals')]
    public function getInvoicesAdvanceTotals(Order $order): array
    {
        return $this->calculator->getInvoicesAdvanceTotals($order);
    }

    #[AsTwigFilter('remaining_totals')]
    public function calculateRemainingTotals(Order $order): array
    {
        return $this->calculator->calculateRemainingTotals($order);
    }
}
