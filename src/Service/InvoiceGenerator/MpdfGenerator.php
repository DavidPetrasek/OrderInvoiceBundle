<?php
namespace Psys\OrderInvoiceBundle\Service\InvoiceGenerator;

use Mpdf\Output\Destination;
use Psys\OrderInvoiceBundle\Service\InvoiceGenerator\PdfGeneratorInterface;


class MpdfGenerator implements PdfGeneratorInterface
{
    public function generate(string $html): string
    {
        try 
        {
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html);

            return $mpdf->Output('', Destination::STRING_RETURN);
        }
        catch (\Mpdf\MpdfException $e) 
        {
            throw new PdfGeneratorException('The MpdfGenerator was unable to generate the invoice PDF: ' . $e->getMessage(), previous: $e);
        }
    }
}