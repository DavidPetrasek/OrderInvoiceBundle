<?php
namespace Psys\OrderInvoiceBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
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
                ->stringNode('file_entity')->defaultValue(null)->end()
                ->stringNode('invoice_binary_provider')->defaultValue(null)->end()
                
                // Optional
                ->arrayNode('storage_path')->addDefaultsIfNotSet()
                    ->children()
                        ->stringNode('proforma')->end()
                        ->stringNode('final')->end()
                    ->end()
                

            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');
        $container->parameters()
            ->set('oi.file_entity', $config['file_entity'])
            ->set('oi.storage_path', $config['storage_path']);

        if (!empty($config['invoice_binary_provider']))
        {
            $builder->setAlias('oi.invoice_binary_provider', $config['invoice_binary_provider']);
            $container->import('../config/styler.php');
        }
    }
}