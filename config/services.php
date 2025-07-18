<?php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psys\OrderInvoiceBundle\Command\ConfigureCommand;
use Psys\OrderInvoiceBundle\Maker\Category;
use Psys\OrderInvoiceBundle\Maker\CronController;
use Psys\OrderInvoiceBundle\Maker\InitDatabase;
use Psys\OrderInvoiceBundle\Service\OrderManager\OrderManager;
use Psys\OrderInvoiceBundle\Repository\OrderRepository;
use Psys\OrderInvoiceBundle\Service\InvoiceManager\InvoiceManager;
use Psys\OrderInvoiceBundle\Service\InvoiceGenerator\MpdfGenerator;
use Psys\Utils\Math;

return function(ContainerConfigurator $container): void 
{
    $container->services()

        ->set('psys_utils.math', Math::class)

        
        ->set(ConfigureCommand::class)
            ->args([
                param('kernel.project_dir'),
                service('filesystem'),
            ])
            ->tag('console.command')
        ->set(InitDatabase::class)
            ->tag('maker.command')
        ->set(Category::class)
            ->tag('maker.command')
        ->set(CronController::class)
            ->tag('maker.command')


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

        ->set('oi.mpdf_generator', MpdfGenerator::class)
            ->alias(MpdfGenerator::class, 'oi.mpdf_generator')
    ;
};