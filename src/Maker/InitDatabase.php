<?php

namespace Psys\OrderInvoiceBundle\Maker;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\ORMDependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;


class InitDatabase extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:oib:init_database';
    }

    public static function getCommandDescription(): string
    {
        return 'Initializes the database for the OrderInvoiceBundle';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('class_name', InputArgument::REQUIRED, 'The name of the new migration class')
        ;
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        // $dependencies->addClassDependency(Form::class, 'symfony/form');
        // ORMDependencyBuilder::buildDependencies($dependencies);
    }
    

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $changePasswordFormTypeClassNameDetails = $generator->createClassNameDetails(
            $input->getArgument('class_name'),
            ''
        );

        $useStatements = new UseStatementGenerator([
            Schema::class,
            AbstractMigration::class,
        ]);

        $generator->generateClass(
            $changePasswordFormTypeClassNameDetails->getFullName(),
            __DIR__.'/Resources/skeleton/MigrationInit.tpl.php',
            ['use_statements' => $useStatements]
        );

        $generator->writeChanges();
    }
}