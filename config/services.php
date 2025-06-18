<?php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psys\OrderInvoiceBundle\Repository\InvoiceRepository;
use Psys\OrderInvoiceBundle\Service\OrderManager\OrderManager;
use Psys\OrderInvoiceBundle\Repository\OrderRepository;
use Psys\OrderInvoiceBundle\Service\InvoiceManager\InvoiceManager;
use Psys\Utils\Math;

return function(ContainerConfigurator $container): void 
{
    $container->services()

        ->set('oi.order_manager', OrderManager::class)
            ->args([
                service('doctrine.orm.default_entity_manager'),
                service('psys_utils.math'),
            ])
            ->alias(OrderManager::class, 'oi.order_manager')
        
         ->set('oi.invoice_manager', InvoiceManager::class)
            ->args([
                service('doctrine.orm.default_entity_manager')
            ])
            ->alias(InvoiceManager::class, 'oi.invoice_manager')
        

        ->set(OrderRepository::class)
            ->args([
                service('doctrine')
            ])
            ->tag('doctrine.repository_service')
        

        ->set('psys_utils.math', Math::class)
    ;
}; 