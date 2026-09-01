<?php

declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;


class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('invoices_advance_totals', [OrderRuntime::class, 'getInvoicesAdvanceTotals']),
        ];
    }
}
