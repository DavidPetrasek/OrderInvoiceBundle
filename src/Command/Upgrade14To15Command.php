<?php
namespace Psys\OrderInvoiceBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use function Symfony\Component\String\u;


#[AsCommand(name: 'oib:upgrade:14_to_15', description: 'Upgrades OrderInvoiceBundle from version 1.3.3 to 1.4')]
class Upgrade14To15Command extends Command
{
    const int DOCTRINE_BATCH_SIZE = 20;
    const int SELECT_BATCH_SIZE = 50;

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

        $transformDb_itemsOwnershipResult = $this->transformDb_itemsOwnership($output);
        if (is_int($transformDb_itemsOwnershipResult)) {return $transformDb_itemsOwnershipResult;}

        $transformDb_moneyResult = $this->transformDb_money($output);
        if (is_int($transformDb_moneyResult)) {return $transformDb_moneyResult;}

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
        if (!empty($finderArr)) // At least one migration exists 
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
        $prepDbMigrationProcess = new Process(['bin/console', 'make:oib:upgrade_14_to_15_prepared_migration', $newMigrationName]);
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
        $output->writeln('Transforming database...');
        $dbConn = $this->em->getConnection();
        $selectOffset = 0;
        
        while (true) 
        {
            $qb = $dbConn->createQueryBuilder()
                    ->select('*')
                    ->from('oi_invoice')
                    ->setFirstResult($selectOffset)
                    ->setMaxResults(self::SELECT_BATCH_SIZE);
                
            $rowsInvoice = $qb->fetchAllAssociative();   if (empty($rowsInvoice)) {break;}
            
            foreach ($rowsInvoice as $rowInvoice)
            {
                $orderID = $dbConn->createQueryBuilder()
                    ->select('id')
                    ->from('oi_order')
                    ->where('invoice_id = :invoice_id')
                        ->setParameter('invoice_id', $rowInvoice['id'])
                    ->fetchOne();
                    
                if (!$orderID) {continue;}

                $sellerID = $dbConn->createQueryBuilder()
                    ->select('id')
                    ->from('oi_seller')
                    ->where('invoice_id = :invoice_id')
                        ->setParameter('invoice_id', $rowInvoice['id'])
                    ->fetchOne();
                $buyerID = $dbConn->createQueryBuilder()
                    ->select('id')
                    ->from('oi_buyer')
                    ->where('invoice_id = :invoice_id')
                        ->setParameter('invoice_id', $rowInvoice['id'])
                    ->fetchOne();

                // Handle missing seller/buyer/order
                $sellerID = ($sellerID !== false) ? $sellerID : null;
                $buyerID  = ($buyerID  !== false) ? $buyerID  : null;

                $dbConn->createQueryBuilder()
                    ->update('oi_order o')
                    ->where('invoice_id = '.$rowInvoice['id'])
                    
                    ->set('o.invoice_proforma_id', ':invoice_proforma_id')
                    ->setParameter('invoice_proforma_id', $rowInvoice['invoice_proforma_id'])

                    ->set('o.invoice_final_id', ':invoice_final_id')
                    ->setParameter('invoice_final_id', $rowInvoice['invoice_final_id'])
                    
                    ->set('o.invoice_regular_id', ':invoice_regular_id')
                    ->setParameter('invoice_regular_id', $rowInvoice['invoice_regular_id'])
                    
                    ->set('o.seller_id', ':seller_id')
                    ->setParameter('seller_id', $sellerID)

                    ->set('o.buyer_id', ':buyer_id')
                    ->setParameter('buyer_id', $buyerID)
                    
                    ->executeStatement();

                // Advance invoices
                $dbConn->createQueryBuilder()
                    ->update('oi_invoice_advance ia')
                    ->where('invoice_id = '.$rowInvoice['id'])
                    ->set('ia.order_id', ':order_id')
                    ->setParameter('order_id', $orderID)
                    ->executeStatement();

                // Payment reference
                if (!empty($rowInvoice['invoice_proforma_id'])) 
                {
                    $dbConn->createQueryBuilder()
                        ->update('oi_invoice_proforma ip')
                        ->where('id = '.$rowInvoice['invoice_proforma_id'])
                        ->set('ip.payment_reference', ':payment_reference')
                        ->setParameter('payment_reference', $rowInvoice['payment_reference'])
                        ->executeStatement();
                }
  
                $dbConn->createQueryBuilder()
                    ->update('oi_invoice_advance ia')
                    ->where('invoice_id = '.$rowInvoice['id'])
                    ->set('ia.payment_reference', ':payment_reference')
                    ->setParameter('payment_reference', $rowInvoice['payment_reference'])
                    ->executeStatement();

                if (!empty($rowInvoice['invoice_final_id'])) 
                {
                    $dbConn->createQueryBuilder()
                        ->update('oi_invoice_final inf')
                        ->where('id = '.$rowInvoice['invoice_final_id'])
                        ->set('inf.payment_reference', ':payment_reference')
                        ->setParameter('payment_reference', $rowInvoice['payment_reference'])
                        ->executeStatement();
                }

                if (!empty($rowInvoice['invoice_regular_id'])) 
                {
                    $dbConn->createQueryBuilder()
                        ->update('oi_invoice_regular ir')
                        ->where('id = '.$rowInvoice['invoice_regular_id'])
                        ->set('ir.payment_reference', ':payment_reference')
                        ->setParameter('payment_reference', $rowInvoice['payment_reference'])
                        ->executeStatement();
                }
            }

            $selectOffset += self::SELECT_BATCH_SIZE;
        }
        
        $output->writeln('Database was transformed...');
        return true;
    }

    public function transformDb_itemsOwnership(OutputInterface $output): bool|int
    {
        $output->writeln('Changing items ownership in the database...');
        $dbConn = $this->em->getConnection();
        $selectOffset = 0;
        
        while (true) 
        {
            $qb = $dbConn->createQueryBuilder()
                    ->select('*')
                    ->from('oi_order')
                    ->setFirstResult($selectOffset)
                    ->setMaxResults(self::SELECT_BATCH_SIZE);
                
            $orders = $qb->fetchAllAssociative();   if (empty($orders)) {break;}
            
            foreach ($orders as $order)
            {
                $orderItems = $dbConn->createQueryBuilder()
                    ->select('*')
                    ->from('oi_item')
                    ->where('order_id = '.$order['id'])
                    ->fetchAllAssociative();

                if (!empty($orderItems[0]['invoice_advance_id'])) {continue;}

                foreach ($orderItems as $orderItem)
                {
                    if (!empty($order['invoice_proforma_id'])) 
                    {
                        // Create copy for proforma invoice and leave order its current items (which represent the total price)
                        $dbConn->createQueryBuilder()
                            ->insert('oi_item')
                            ->setValue('category', '?')
                                ->setParameter(0, $orderItem['category'])
                            ->setValue('name', '?')
                                ->setParameter(1, $orderItem['name'])
                            ->setValue('short_description', '?')
                                ->setParameter(2, $orderItem['short_description'])
                            ->setValue('amount', '?')
                                ->setParameter(3, $orderItem['amount'])
                            ->setValue('price_vat_included', '?')
                                ->setParameter(4, $orderItem['price_vat_included'])
                            ->setValue('price_vat_excluded', '?')
                                ->setParameter(5, $orderItem['price_vat_excluded'])
                            ->setValue('vat_rate', '?')
                                ->setParameter(6, $orderItem['vat_rate'])
                            ->setValue('vat', '?')
                                ->setParameter(7, $orderItem['vat'])
                            ->setValue('amount_type', '?')
                                ->setParameter(8, $orderItem['amount_type'])
                            ->setValue('invoice_proforma_id', '?')
                                ->setParameter(9, $order['invoice_proforma_id'])
                            ->executeStatement();
                    }

                    // Move items from order to the regular invoice
                    else if (!empty($order['invoice_regular_id'])) 
                    {
                        $dbConn->createQueryBuilder()
                            ->update('oi_item it')
                            ->where('id = '.$orderItem['id'])
                            ->set('it.invoice_regular_id', ':invoice_regular_id')
                                ->setParameter('invoice_regular_id', $order['invoice_regular_id'])
                            ->executeStatement();
                        
                        $dbConn->createQueryBuilder()
                            ->update('oi_item it')
                            ->where('id = '.$orderItem['id'])
                            ->set('it.order_id', ':order_id')
                                ->setParameter('order_id', null)
                            ->executeStatement();
                    }
                }
            }

            $selectOffset += self::SELECT_BATCH_SIZE;
        }
        
        $output->writeln('Items ownership was changed...');
        return true;
    }
    
    public function transformDb_money(OutputInterface $output): bool|int
    {
        $output->writeln('Moving/copiyng money related data...');
        $dbConn = $this->em->getConnection();
        $selectOffset = 0;
        
        while (true) 
        {
            $qb = $dbConn->createQueryBuilder()
                    ->select('*')
                    ->from('oi_order')
                    ->setFirstResult($selectOffset)
                    ->setMaxResults(self::SELECT_BATCH_SIZE);
                
            $orders = $qb->fetchAllAssociative();   if (empty($orders)) {break;}
            
            foreach ($orders as $order)
            {
                if (!empty($order['invoice_proforma_id'])) 
                {
                    // Copy to proforma and leave order unchanged
                    $dbConn->createQueryBuilder()
                        ->update('oi_invoice_proforma prof')
                        ->where('id = '.$order['invoice_proforma_id'])
                        ->set('prof.price_vat_included', ':price_vat_included')
                            ->setParameter('price_vat_included', $order['price_vat_included'])
                        ->set('prof.price_vat_excluded', ':price_vat_excluded')
                            ->setParameter('price_vat_excluded', $order['price_vat_excluded'])
                        ->set('prof.price_vat_base', ':price_vat_base')
                            ->setParameter('price_vat_base', $order['price_vat_base'])
                        ->set('prof.price_vat', ':price_vat')
                            ->setParameter('price_vat', $order['price_vat'])
                        ->set('prof.currency', ':currency')
                            ->setParameter('currency', $order['currency'])
                        ->executeStatement();
                    
                    $proformaPayable = $dbConn->createQueryBuilder()
                        ->select('payable')
                        ->from('oi_invoice_proforma')
                        ->where('id = '.$order['invoice_proforma_id'])
                        ->fetchOne();
                    if ($proformaPayable == 1)
                    {
                        $dbConn->createQueryBuilder()
                            ->update('oi_invoice_proforma prof')
                            ->where('id = '.$order['invoice_proforma_id'])
                            ->set('prof.paid_at', ':paid_at')
                                ->setParameter('paid_at', $order['paid_at'])
                            ->set('prof.payment_mode', ':payment_mode')
                                ->setParameter('payment_mode', $order['payment_mode'])
                            ->set('prof.payment_mode_bank_account', ':payment_mode_bank_account')
                                ->setParameter('payment_mode_bank_account', $order['payment_mode_bank_account'])
                            ->executeStatement();
                    }
                }

                if (!empty($order['invoice_regular_id'])) 
                {
                    // Copy to regular
                    $dbConn->createQueryBuilder()
                        ->update('oi_invoice_regular reg')
                        ->where('id = '.$order['invoice_regular_id'])
                        ->set('reg.paid_at', ':paid_at')
                            ->setParameter('paid_at', $order['paid_at'])
                        ->set('reg.payment_mode', ':payment_mode')
                            ->setParameter('payment_mode', $order['payment_mode'])
                        ->set('reg.payment_mode_bank_account', ':payment_mode_bank_account')
                            ->setParameter('payment_mode_bank_account', $order['payment_mode_bank_account'])
                        ->set('reg.price_vat_included', ':price_vat_included')
                            ->setParameter('price_vat_included', $order['price_vat_included'])
                        ->set('reg.price_vat_excluded', ':price_vat_excluded')
                            ->setParameter('price_vat_excluded', $order['price_vat_excluded'])
                        ->set('reg.price_vat_base', ':price_vat_base')
                            ->setParameter('price_vat_base', $order['price_vat_base'])
                        ->set('reg.price_vat', ':price_vat')
                            ->setParameter('price_vat', $order['price_vat'])
                        ->set('reg.currency', ':currency')
                            ->setParameter('currency', $order['currency'])
                        ->executeStatement();
                    
                    // Remove from order
                    $dbConn->createQueryBuilder()
                        ->update('oi_order o')
                        ->where('id = '.$order['id'])
                        ->set('o.paid_at', ':paid_at')
                            ->setParameter('paid_at', null)
                        ->set('o.payment_mode', ':payment_mode')
                            ->setParameter('payment_mode', null)
                        ->set('o.payment_mode_bank_account', ':payment_mode_bank_account')
                            ->setParameter('payment_mode_bank_account', null)
                        ->set('o.price_vat_included', ':price_vat_included')
                            ->setParameter('price_vat_included', null)
                        ->set('o.price_vat_excluded', ':price_vat_excluded')
                            ->setParameter('price_vat_excluded', null)
                        ->set('o.price_vat_base', ':price_vat_base')
                            ->setParameter('price_vat_base', null)
                        ->set('o.price_vat', ':price_vat')
                            ->setParameter('price_vat', null)
                        ->set('o.currency', ':currency')
                            ->setParameter('currency', null)
                        ->executeStatement();
                }
            }

            $selectOffset += self::SELECT_BATCH_SIZE;
        }
        
        $output->writeln('Done moving/copiyng money related data...');
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
