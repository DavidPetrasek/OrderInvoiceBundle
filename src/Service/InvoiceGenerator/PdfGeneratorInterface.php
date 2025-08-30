<?php
namespace Psys\OrderInvoiceBundle\Service\InvoiceGenerator;


interface PdfGeneratorInterface
{
    /**
     * Convert an HTML string into raw PDF binary data.
     *
     * @param string $html HTML markup to convert.
     * @return string Raw PDF bytes.
     * 
     * @throws PdfGeneratorException If PDF generation fails.
     */
    public function generate(string $html, array $options = []): string;
}