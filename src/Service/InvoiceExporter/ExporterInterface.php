<?php
namespace Psys\OrderInvoiceBundle\Service\InvoiceExporter;

use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;
use Psys\OrderInvoiceBundle\Model\Invoice\ExportMode;

interface ExporterInterface
{
    public function export(Order $order, InvoiceType $invoiceType, ExportMode $mpdfExportMode, ?string $downloadFilename, ?string $fileAbsPath) : mixed;
}