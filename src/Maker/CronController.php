<?php

namespace Psys\OrderInvoiceBundle\Maker;

use Psys\OrderInvoiceBundle\Service\InvoiceManager\InvoiceManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\ORMDependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'make:oib:cron_controller';
    }

    public static function getCommandDescription(): string
    {
        return 'Creates a cron controller for the OrderInvoiceBundle';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $changePasswordFormTypeClassNameDetails = $generator->createClassNameDetails(
            'CronOibController',
            'Controller'
        );

        $useStatements = new UseStatementGenerator([
            AbstractController::class,
            Request::class,
            Response::class,
            Route::class,
            InvoiceManager::class,
        ]);

        $generator->generateClass(
            $changePasswordFormTypeClassNameDetails->getFullName(),
            __DIR__.'/Resources/skeleton/CronController.tpl.php',
            ['use_statements' => $useStatements]
        );

        $generator->writeChanges();
    }
}