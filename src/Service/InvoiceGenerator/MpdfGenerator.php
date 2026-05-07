<?php
namespace Psys\OrderInvoiceBundle\Service\InvoiceGenerator;

use Mpdf\Output\Destination;
use Mpdf\HTMLParserMode;

class MpdfGenerator
{
    private ?string $cssFilePath = null;

    /**
     * Set the absolute path to the CSS stylesheet.
     * 
     * @param string $cssFilePath Absolute path to the CSS file.
     * @return self
     */
    public function useCss(string $cssFilePath): self
    {
        $this->cssFilePath = $cssFilePath;
        
        return $this;
    }

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

            // Check if CSS file path is set and process it
            if ($this->cssFilePath !== null) 
            {
                if (!file_exists($this->cssFilePath)) 
                {
                    throw new \RuntimeException(sprintf('The CSS file was not found at the specified path: "%s"', $this->cssFilePath));
                }

                $stylesheet = file_get_contents($this->cssFilePath);
                
                $mpdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
                $mpdf->WriteHTML($html, HTMLParserMode::HTML_BODY);
            } 
            else 
            {
                // Fallback to standard writing if no CSS is provided
                $mpdf->WriteHTML($html);
            }

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