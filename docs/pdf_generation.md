[Back to index](../README.md)

PDF generation
==============
- Generator returns binary data

Available generators: 
- MpdfGenerator

Available commands to generate templates:

`symfony console make:oib:invoice:mpdf_twig_template`

---------
Example using the MpdfGenerator:

``` php
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use Psys\OrderInvoiceBundle\Service\InvoiceGenerator\MpdfGenerator;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;
use App\Entity\MyFileEntity;

public function generateProformaInvoiceBinaryPdf (MpdfGenerator $mpdfGenerator, Order $ent_Order, Environment $twig): string
{
    $htmlPDF = $twig->render('invoice/oi_mpdf_default.html.twig', 
        [
            'ent_Order'  => $ent_Order,
            'invoiceType'  => InvoiceType::PROFORMA->name,
        ]); 
    return $mpdfGenerator->generate($htmlPDF, 
                // Optional mPDF config
                [
                    'margin_left' => 0,
                    'margin_right' => 0,
                ],
                // Optional callback to add background graphics 
                function (\Mpdf\Mpdf $mpdf) 
                {
                    $mpdf->SetFillColor(0, 200, 255);
                    $mpdf->RoundedRect(20, 30, 30, 30, 0, 'F');
                },
                // Optional callback to add overlay graphics 
                function (\Mpdf\Mpdf $mpdf) 
                {
                    $mpdf->SetFillColor(0, 255, 50);
                    $mpdf->RoundedRect(35, 45, 30, 30, 0, 'F');
                }
            );
}
```