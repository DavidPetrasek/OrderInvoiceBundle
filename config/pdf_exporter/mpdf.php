<?php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psys\OrderInvoiceBundle\Service\InvoiceExporter\MpdfExporter;


return function(ContainerConfigurator $container): void 
{
    $container->services()

        ->set('oi.pdf_exporter', MpdfExporter::class)
            ->args([
                service('twig'),
                param('pdf_exporter.template_path')
            ])
            ->alias(MpdfExporter::class, 'oi.pdf_exporter')
    ;
}; 