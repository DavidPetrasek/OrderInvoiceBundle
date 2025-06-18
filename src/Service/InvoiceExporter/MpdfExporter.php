<?php
namespace Psys\OrderInvoiceBundle\Service\InvoiceExporter;

use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\Invoice\ExportMode;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;
use Twig\Environment;


class MpdfExporter implements ExporterInterface
{    
    public function __construct
    (
        private readonly Environment $twig,
        private readonly string $templatePath
    )
    {}

    public function export(Order $order, InvoiceType $invoiceType, ExportMode $mpdfExportMode, ?string $downloadFilename = 'invoice.pdf', ?string $fileAbsPath = null) : mixed
    {        
        $html = $this->twig->render($this->templatePath, 
        [
            'ent_Order'  => $order,
            'invoiceType'  => $invoiceType->name,
        ]);

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($html);

        if ($mpdfExportMode === ExportMode::BINARY)
        {
            return $mpdf->OutputBinaryData();
        }
        else if ($mpdfExportMode === ExportMode::INLINE) 
        {
            return $mpdf->OutputHttpInline();
        }
        else if ($mpdfExportMode === ExportMode::DOWNLOAD)
        {
            return $mpdf->OutputHttpDownload($downloadFilename);
        }
        else if ($mpdfExportMode === ExportMode::FILE)
        {
            return $mpdf->OutputFile($fileAbsPath);
        }

        return true;
    }
}