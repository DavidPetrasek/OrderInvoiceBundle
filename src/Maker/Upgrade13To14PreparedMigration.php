<?php

namespace Psys\OrderInvoiceBundle\Maker;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;


class Upgrade13To14PreparedMigration extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:oib:upgrade_13_to_14_prepared_migration';
    }

    public static function getCommandDescription(): string
    {
        return 'Updates the database during upgrade from version 1.3.3 to 1.4';
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
            __DIR__.'/Resources/skeleton/Upgrade13To14PreparedMigration.tpl.php',
            ['use_statements' => $useStatements]
        );

        $generator->writeChanges();
    }
}