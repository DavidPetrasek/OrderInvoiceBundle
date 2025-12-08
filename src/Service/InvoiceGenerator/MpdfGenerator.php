<?php
namespace Psys\OrderInvoiceBundle\Service\InvoiceGenerator;

use Mpdf\Output\Destination;


class MpdfGenerator
{
    /**
     * Convert an HTML string into raw PDF binary data.
     *
     * @param string $html HTML markup to convert.
     * @param array $options Optional mPDF options
     * @param callable|null $clbBeforeRender Optional callback executed before rendering HTML. Receives the mPDF instance as parameter.
     * @param callable|null $clbAfterRender Optional callback executed after rendering HTML. Receives the mPDF instance as parameter.
     * 
     * @return string Raw PDF bytes.
     * 
     * @throws PdfGeneratorException If PDF generation fails.
     */
    public function generate(string $html, array $options = [], ?callable $clbBeforeRender = null, ?callable $clbAfterRender = null): string
    {
        try
        {
            $mpdf = new \Mpdf\Mpdf($options);

            if ($clbBeforeRender) 
            {
                $mpdf->AddPage();
                $clbBeforeRender($mpdf);
            }

            $mpdf->WriteHTML($html);

            if ($clbAfterRender) 
            {
                $clbAfterRender($mpdf);
            }

            return $mpdf->Output('', Destination::STRING_RETURN);
        }
        catch (\Mpdf\MpdfException $e) 
        {
            throw new PdfGeneratorException('The MpdfGenerator was unable to generate the invoice PDF: ' . $e->getMessage(), previous: $e);
        }
    }
}