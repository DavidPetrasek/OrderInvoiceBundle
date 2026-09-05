<?php declare(strict_types=1);

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
        $templatePath = $this->projectDir . '/templates/invoice/oi_mpdf_default.html.twig';

        // Remove existing template file to allow automatic overwrite
        if (is_file($templatePath))
        {
            unlink($templatePath);
        }

        $generator->generateFile(
            $templatePath,
            __DIR__ . '/Resources/skeleton/InvoiceMpdfTwig.tpl.html.twig',
            []
        );

        if ($this->chosenStyle !== 'none')
        {
            $cssPath = $this->projectDir . '/assets/css/invoice/' . $this->chosenStyle . '_mpdf.css';

            // Remove existing CSS file to allow automatic overwrite
            if (is_file($cssPath))
            {
                unlink($cssPath);
            }

            $generator->generateFile(
                $cssPath,
                __DIR__ . '/Resources/skeleton/style_' . $this->chosenStyle . '_mpdf.tpl.css'
            );
        }

        $generator->writeChanges();
    }
}