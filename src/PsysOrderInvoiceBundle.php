<?php

namespace Psys\OrderInvoiceBundle;

use Psys\OrderInvoiceBundle\DependencyInjection\Compiler\ResolveTargetEntityPass;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;


class PsysOrderInvoiceBundle extends AbstractBundle
{
    private const EXPORTER_ENGINES = ['mpdf'];

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
           ->children()
                ->arrayNode('pdf_exporter')->isRequired()
                    ->children()
                        ->scalarNode('engine')->end()
                        ->scalarNode('class')->end()
                        ->scalarNode('template_path')->isRequired()->cannotBeOverwritten()->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->parameters()
            ->set('pdf_exporter.template_path', $config['pdf_exporter']['template_path']);

        // Set an alias for a custom registered pdf exporter service
        if (!empty($config['pdf_exporter']['class']))
        {
            $builder->setAlias('oi.pdf_exporter', $config['pdf_exporter']['class']);
        }
        // Register one of default pdf exporters if specified
        else if (in_array(strtolower((string) $config['pdf_exporter']['engine']), self::EXPORTER_ENGINES)) 
        {
            $container->import('../config/pdf_exporter/'.$config['pdf_exporter']['engine'].'.php');
        }
    }
}