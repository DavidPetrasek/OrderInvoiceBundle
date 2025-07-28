<?php

namespace Psys\OrderInvoiceBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;


class InvoiceMpdfTwigTemplate extends AbstractMaker
{
    public function __construct
    (
        private string $projectDir,
    )
    {}

    public static function getCommandName(): string
    {
        return 'make:oib:invoice:mpdf_twig_template';
    }

    public static function getCommandDescription(): string
    {
        return 'Generate basic invoice Twig template for use with the Mpdf generator';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $generator->generateFile(
             $this->projectDir.'/templates/invoice/oi_mpdf_default.html.twig',
            __DIR__.'/Resources/skeleton/InvoiceMpdfTwig.tpl.html.twig',
            []
        );

        $generator->writeChanges();
    }
}