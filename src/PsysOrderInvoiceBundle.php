<?php declare(strict_types=1);

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
                        ->stringNode('advance')->end()
                        ->stringNode('final')->end()
                        ->stringNode('regular')->end()
                    ->end()


            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()
            ->set('oi.file_entity', $config['file_entity'])
            ->set('oi.storage_path', $config['storage_path']);

        if (is_string($config['invoice_binary_provider']))
        {
            $builder->setAlias('oi.invoice_binary_provider', $config['invoice_binary_provider']);
        }

        $container->import('../config/services.php');
    }
}
