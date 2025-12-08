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
    // private bool $chosenWantAnyStyle;
    // private int $chosenStyleMode;
    private string $chosenTemplateName = 'oi_mpdf_default';
    // private string $chosenTwigProjectDirVarName;

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

    // public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    // {
    //     // TODO: TOTO JE ASI NESMYSL
    //     $this->chosenWantAnyStyle = $io->choice('Do you want any style?', ['none', 'default'], 1);
        
    //     if ($this->chosenWantAnyStyle)
    //     {
    //         $this->chosenStyleMode = $io->choice('How do you wish to style the template?', ['separate stylesheet'], 0);

    //         dump($this->chosenStyleMode);
    //         if ($this->chosenStyleMode === 0) 
    //         {
    //              $this->chosenTwigProjectDirVarName = $io->ask('Project directory variable name (you need to pass this variable to the template yourself)', 'projectDir');
    //         }
    //     }

    //     $this->chosenTemplateName = $io->ask('Template filename', 'oi_mpdf_default');
    // }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $generator->generateFile(
             $this->projectDir.'/templates/invoice/'.$this->chosenTemplateName.'.html.twig',
            __DIR__.'/Resources/skeleton/InvoiceMpdfTwig.tpl.html.twig',
            []
        );

        // if ($this->chosenWantAnyStyle)
        // {
        //     if ($this->chosenStyleMode === 0) 
        //     {
        //         $generator->generateFile(
        //             $this->projectDir.'/assets/css/invoice/'.$this->chosenTemplateName.'.css',
        //             __DIR__.'/Resources/skeleton/InvoiceMpdfStylesheet.css.twig',
        //             [
        //                 'projectDirVarName' => $this->chosenTwigProjectDirVarName
        //             ]
        //         );
        //     }
        // }

        $generator->writeChanges();
    }
}