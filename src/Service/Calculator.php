<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Service;

use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceRegular;
use Psys\OrderInvoiceBundle\Entity\Item;

class Calculator
{    
    public function __construct
    (
        private readonly Math $math
    )
    {} 

    /**
     * Adds up totals of all advance invoices
     */
    public function getInvoicesAdvanceTotals(Order $order): array
    {
        $advanceTotals = [
            'vatIncluded' => '0.00',
            'vatExcluded' => '0.00',
            'vatBase'     => '0.00',
            'vat'         => '0.00',
        ];

        foreach ($order->getInvoicesAdvance() as $invoiceAdvance) 
        {
            $advanceTotals['vatIncluded'] = bcadd($advanceTotals['vatIncluded'], $invoiceAdvance->getPriceVatIncluded(), 2);
            $advanceTotals['vatExcluded'] = bcadd($advanceTotals['vatExcluded'], $invoiceAdvance->getPriceVatExcluded(), 2);
            $advanceTotals['vatBase']     = bcadd($advanceTotals['vatBase'], $invoiceAdvance->getPriceVatBase(), 2);
            $advanceTotals['vat']         = bcadd($advanceTotals['vat'], $invoiceAdvance->getPriceVat(), 2);
        }

        return $advanceTotals;
    }

    public function calculateRemainingTotals(Order $order): array
    {
        $deductedNet = '0.00';
        $deductedVat = '0.00';
        $deductedIncl = '0.00';

        // 1. Deduct advance invoices if present
        if (!$order->getInvoicesAdvance()->isEmpty())
        {
            $advancesTotals = $this->getInvoicesAdvanceTotals($order);
            $deductedNet = $advancesTotals['vatExcluded'];
            $deductedVat = $advancesTotals['vat'];
            $deductedIncl = $advancesTotals['vatIncluded'];
        }
        // 2. Deduct proforma invoice if present and payable
        else if ($order->getInvoiceProforma() instanceof InvoiceProforma && $order->getInvoiceProforma()->isPayable())
        {
            $proforma = $order->getInvoiceProforma();
            $deductedNet = $proforma->getPriceVatExcluded();
            $deductedVat = $proforma->getPriceVat();
            $deductedIncl = $proforma->getPriceVatIncluded();
        }

        $remainingNet = bcsub($order->getPriceVatExcluded(), $deductedNet, 2);
        $remainingVat = bcsub($order->getPriceVat(), $deductedVat, 2);
        $remainingIncl = bcsub($order->getPriceVatIncluded(), $deductedIncl, 2);

        $isCredit = bccomp($remainingIncl, '0.00', 2) < 0;

        return [
            'net'          => $remainingNet,
            'vat'          => $remainingVat,
            'incl'         => $remainingIncl,
            'isCredit'     => $isCredit,
            'creditAmount' => $isCredit ? bcmul($remainingIncl, '-1', 2) : '0.00',
        ];
    }

    public function calculateTotals(Order|InvoiceProforma|InvoiceAdvance|InvoiceRegular $ent): array
    {
        $priceVatExcludedTotal = '0.00';
        $priceVatIncludedTotal = '0.00';
        $vatBase = '0.00';

        foreach ($ent->getItems() as $item)
        {
            $itemTotals = $this->calculateItemTotals($item);
            $amount = (string) $item->getAmount();

            $itemVatExcluded = bcmul($itemTotals['priceVatExcluded'], $amount, 2);
            $itemVatIncluded = bcmul($itemTotals['priceVatIncluded'], $amount, 2);

            if (bccomp($item->getVatRate(), '0.00', 2) > 0)
            {
                $vatBase = bcadd($vatBase, $itemVatExcluded, 2);
            }

            $priceVatIncludedTotal = bcadd($priceVatIncludedTotal, $itemVatIncluded, 2);
            $priceVatExcludedTotal = bcadd($priceVatExcludedTotal, $itemVatExcluded, 2);
        }

        $vatTotal = bcsub($priceVatIncludedTotal, $priceVatExcludedTotal, 2);

        return [
            'vatIncluded' => $priceVatIncludedTotal,
            'vatExcluded' => $priceVatExcludedTotal,
            'vatBase' => $vatBase,
            'vat' => $vatTotal,
        ];
    }

    private function calculateItemTotals(Item $item): array
    {
        $priceVatIncluded = $item->getPriceVatIncluded();
        $priceVatExcluded = $item->getPriceVatExcluded();
        $vatRate = $item->getVatRate();

        // Calculate price exclusive of VAT from price inclusive of VAT
        if ($priceVatIncluded !== '' && bccomp($priceVatIncluded, '0.00', 2) !== 0)
        {
            $priceVatExcluded = $this->math->extractPercentage($priceVatIncluded, $vatRate, 2);
            $item->setPriceVatExcluded($priceVatExcluded);
        }
        // Calculate price inclusive of VAT from price exclusive of VAT
        else if ($priceVatExcluded !== '' && bccomp($priceVatExcluded, '0.00', 2) !== 0)
        {
            $priceVatIncluded = $this->math->addPercentage($priceVatExcluded, $vatRate, 2);
            $item->setPriceVatIncluded($priceVatIncluded);
        }

        $vat = bcsub($priceVatIncluded, $priceVatExcluded, 2);
        $item->setVat($vat);

        return [
            'priceVatIncluded' => $priceVatIncluded,
            'priceVatExcluded' => $priceVatExcluded,
        ];
    }
}