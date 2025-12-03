<?php

namespace Psys\OrderInvoiceBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;


class PsysOrderInvoiceBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                // Required
                ->stringNode('file_entity')->cannotBeOverwritten()->end()
                
                // Optional
                ->arrayNode('storage_path')
                    ->children()
                        ->stringNode('proforma')->cannotBeOverwritten()->end()
                        ->stringNode('final')->cannotBeOverwritten()->end()
                    ->end()
                

            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');
        $container->parameters()
            ->set('oi.file_entity', $config['file_entity'])
            ->set('oi.storage_path', $config['storage_path'])
            ;
    }
}