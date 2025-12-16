<?php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psys\OrderInvoiceBundle\Command\InstallCommand;
use Psys\OrderInvoiceBundle\Command\StylerEnableCommand;
use Psys\OrderInvoiceBundle\Command\Upgrade12To13Command;
use Psys\OrderInvoiceBundle\Controller\Dev\OibStylerController;
use Psys\OrderInvoiceBundle\Maker\Category;
use Psys\OrderInvoiceBundle\Maker\CronController;
use Psys\OrderInvoiceBundle\Maker\InitDatabase;
use Psys\OrderInvoiceBundle\Maker\InvoiceMpdfTwigTemplate;
use Psys\OrderInvoiceBundle\Service\OrderManager\OrderManager;
use Psys\OrderInvoiceBundle\Repository\OrderRepository;
use Psys\OrderInvoiceBundle\Service\FileDeleter\FileDeleter;
use Psys\OrderInvoiceBundle\Service\FilePersister\FilePersister;
use Psys\OrderInvoiceBundle\Service\InvoiceManager\InvoiceManager;
use Psys\OrderInvoiceBundle\Service\InvoiceGenerator\MpdfGenerator;
use Psys\Utils\Math;

return function(ContainerConfigurator $container): void 
{
    $services = $container->services();

    $services
        ->set('psys_utils.math', Math::class)
        
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

        ->set('oi.file_persister', FilePersister::class)
            ->args([
                service('filesystem'),
                service('doctrine.orm.default_entity_manager'),
                service('oi.file_deleter'),
                param('kernel.project_dir'),
                param('oi.file_entity'),
                param('oi.storage_path')
            ])
            ->alias(FilePersister::class, 'oi.file_persister')

        ->set('oi.file_deleter', FileDeleter::class)
            ->args([
                service('filesystem'),
                service('doctrine.orm.default_entity_manager'),
                param('kernel.project_dir'),
                param('oi.storage_path')
            ])
            ->alias(FileDeleter::class, 'oi.file_deleter')
    ;

    if ('dev' === $container->env()) 
    {
        $services
            ->set(InstallCommand::class)
            ->args([
                param('kernel.project_dir'),
                service('filesystem'),
            ])
            ->tag('console.command')
        
            ->set(Upgrade12To13Command::class)
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

            ->set(InvoiceMpdfTwigTemplate::class)
                ->args([
                    param('kernel.project_dir'),
                ])
                ->tag('maker.command')

            ->set(StylerEnableCommand::class)
                ->args([
                    param('kernel.project_dir'),
                    service('filesystem')
                ])
                ->tag('console.command')
            
            ->set(OibStylerController::class)
                ->public()
                ->autowire(true)
                ->autoconfigure(true)
                ->args([
                    service('oi.invoice_binary_provider'),
                ]);
    }
};