<?php
namespace Psys\OrderInvoiceBundle\DependencyInjection\Compiler;

use Psys\OrderInvoiceBundle\Model\CustomerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;


// THIS CURRENTLY DOES NOT WORK
//
class ResolveTargetEntityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $target = $container->getParameter('oi.customer_class');

        $config = $container->getExtensionConfig('doctrine');

        $mapping = $config[0]['orm']['resolve_target_entities'] ?? [];

        $mapping[CustomerInterface::class] = $target;

        $container->setParameter('doctrine.orm.resolve_target_entities', $mapping);
    }
}