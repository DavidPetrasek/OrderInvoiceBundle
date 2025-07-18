<?php

namespace Psys\OrderInvoiceBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\ORMDependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class Category extends AbstractMaker
{
    private string $chosenEntity;
    private string $chosenNamespace;

    public static function getCommandName(): string
    {
        return 'make:oib:category';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates enum to specify custom categories for orders or order items';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }
    
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
        $this->chosenEntity = $io->choice('Select entity for which you want to create a new category', ['Order', 'OrderItem']);
        $this->chosenNamespace = $io->ask('Choose namespace for this new category (without App\)', 'Model\OrderInvoiceBundle');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $changePasswordFormTypeClassNameDetails = $generator->createClassNameDetails(
            $this->chosenEntity.'Category',
            $this->chosenNamespace
        );

        $useStatements = new UseStatementGenerator([
            'Psys\OrderInvoiceBundle\Model\\'.$this->chosenEntity.'\CategoryInterface',
        ]);

        $generator->generateClass(
            $changePasswordFormTypeClassNameDetails->getFullName(),
            __DIR__.'/Resources/skeleton/Category.tpl.php',
            ['use_statements' => $useStatements]
        );

        $generator->writeChanges();
    }
}