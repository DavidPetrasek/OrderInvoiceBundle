<?php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psys\OrderInvoiceBundle\Controller\Dev\OibStylerController;

return function(ContainerConfigurator $container): void 
{
    $container->services()

        ->set(OibStylerController::class)
            ->public()
            ->autowire(true)
            ->autoconfigure(true)
            ->args([
                service('oi.invoice_binary_provider'),
            ])
    ;
};