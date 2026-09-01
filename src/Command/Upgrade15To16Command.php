<?php

declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use function Symfony\Component\String\u;


#[AsCommand(name: 'oib:upgrade:15_to_16', description: 'Upgrades OrderInvoiceBundle from version 1.3.3 to 1.4')]
class Upgrade15To16Command extends Command
{
    private const SELECT_BATCH_SIZE = 50;

    public function __construct
    (
        private readonly string $projectDir,
        private readonly Filesystem $filesystem,
        private readonly EntityManagerInterface $em
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('max_execution_time', 60*20);

        $process = new Process(['git', 'update-index', '--refresh']);
        $process->run();
        $process = new Process(['git', 'diff-index', '--quiet', 'HEAD', '--']);
        $process->run();

        if (!$process->isSuccessful())
        {
            $output->writeln('<error>You have uncommitted changes. Please commit them first.</error>');
            return Command::FAILURE;
        }

        $generatePreparedMigrationResult = $this->generatePreparedMigration($output);
        if (is_int($generatePreparedMigrationResult)) {return $generatePreparedMigrationResult;}

        $applyMigrationsResult = $this->applyMigrations($output);
        if (is_int($applyMigrationsResult)) {return $applyMigrationsResult;}

        $transformDbResult = $this->transformDb($output);
        if (is_int($transformDbResult)) {return $transformDbResult;}

        $generateMigrationResult = $this->generateMigration($output);
        if (is_int($generateMigrationResult)) {return $generateMigrationResult;}

        $applyMigrationsResult = $this->applyMigrations($output);
        if (is_int($applyMigrationsResult)) {return $applyMigrationsResult;}

        $output->writeln(PHP_EOL.'<info>✅ Upgrade complete!</info>');
        return Command::SUCCESS;
    }


    private function generatePreparedMigration(OutputInterface $output): bool|int
    {
        // Generate init migration
        $migrationsDir = $this->projectDir.'/migrations';
        $finder = new Finder();
        $finder->files()->in($migrationsDir)->sortByChangedTime()->reverseSorting();  
        $finderArr = iterator_to_array($finder);
        if ($finderArr !== []) // At least one migration exists 
        {
            $latestMigration = $finderArr[array_key_first($finderArr)];
            $latestMigrationDateStr = u($latestMigration)->match('/Version(\d+)/')[1];
            $DTI_latestMigration = new \DateTimeImmutable($latestMigrationDateStr);
            $DTI_newMigration = $DTI_latestMigration->modify('+1 second');
        }
        else // No existing migrations
        {
            $DTI_newMigration = new \DateTimeImmutable();
        }
        $newMigrationName = 'Version'.$DTI_newMigration->format('YmdHis');
        
        $output->writeln('Generating prepared migration ...');
        $prepDbMigrationProcess = new Process(['bin/console', 'make:oib:upgrade_15_to_16_prepared_migration', $newMigrationName]);
        $prepDbMigrationProcess->run();
        if (!$prepDbMigrationProcess->isSuccessful()) 
        {
            $output->writeln('<error>Failed to generate migration:</error>');
            $output->writeln($prepDbMigrationProcess->getErrorOutput());
            return Command::FAILURE;
        }

        $this->filesystem->rename(
            $this->projectDir.'/src/'.$newMigrationName.'.php',
            $migrationsDir.'/'.$newMigrationName.'.php',
            true
        );

        $output->writeln('<info>Prepared migration generated!</info>');

        return true;
    }
    
    public function transformDb(OutputInterface $output): bool|int
    {
        $output->writeln('Copying paid_at...');
        $dbConn = $this->em->getConnection();
        $selectOffset = 0;
        
        while (true) 
        {
            $qb = $dbConn->createQueryBuilder()
                    ->select('*')
                    ->from('oi_order')
                    ->setFirstResult($selectOffset)
                    ->setMaxResults(self::SELECT_BATCH_SIZE);
                
            $ordersBatch = $qb->fetchAllAssociative();   if ($ordersBatch === []) {break;}
            
            foreach ($ordersBatch as $order)
            {
                if (!empty($order['invoice_final_id'])) 
                {
                    $dbConn->createQueryBuilder()
                        ->update('oi_invoice_final final')
                        ->where('id = '.$order['invoice_final_id'])
                        ->set('final.paid_at', ':paid_at')
                            ->setParameter('paid_at', $order['paid_at'])
                        ->executeStatement();
                }
            }

            $selectOffset += self::SELECT_BATCH_SIZE;
        }
        
        $output->writeln('paid_at copied...');
        return true;
    }

    private function generateMigration(OutputInterface $output): bool|int
    {
        $output->writeln('Generating migration...');
        $makeMigrationProcess = new Process(['bin/console', 'make:migration']);
        $makeMigrationProcess->run();

        if (!$makeMigrationProcess->isSuccessful()) 
        {
            $output->writeln('<error>Failed to generate migration:</error>');
            $output->writeln($makeMigrationProcess->getErrorOutput());
            return Command::FAILURE;
        }
        $output->writeln('<info>Migration generated!</info>');

        return true;
    }

    private function applyMigrations(OutputInterface $output): bool|int
    {
        $output->writeln('Applying migrations...');
        $applyMigrationProcess = new Process(['bin/console', 'doctrine:migrations:migrate', '--no-interaction']);
        $applyMigrationProcess->run();
        if (!$applyMigrationProcess->isSuccessful()) 
        {
            $output->writeln('<error>Failed to apply migration:</error>');
            $output->writeln($applyMigrationProcess->getErrorOutput());
            return Command::FAILURE;
        }

        $output->writeln('<info>Migrations applied!</info>');

        return true;
    }
}
