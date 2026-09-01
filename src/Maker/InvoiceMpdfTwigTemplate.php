<?php

declare(strict_types=1);

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
    private string $chosenStyle;
    private const STYLES = ['none', 'default'];

    public function __construct
    (
        private readonly string $projectDir,
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

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->chosenStyle = $io->choice('Select style', self::STYLES, 1);
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $generator->generateFile(
             $this->projectDir.'/templates/invoice/oi_mpdf_default.html.twig',
            __DIR__.'/Resources/skeleton/InvoiceMpdfTwig.tpl.html.twig',
            []
        );

        if ($this->chosenStyle !== 'none')
        {
            $generator->generateFile(
                $this->projectDir.'/assets/css/invoice/'.$this->chosenStyle.'_mpdf.css',
                __DIR__.'/Resources/skeleton/style_'.$this->chosenStyle.'_mpdf.tpl.css'
            );
        }

        $generator->writeChanges();
    }
}
