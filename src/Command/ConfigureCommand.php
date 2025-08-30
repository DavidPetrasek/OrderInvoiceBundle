<?php
namespace Psys\OrderInvoiceBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use function Symfony\Component\String\u;


#[AsCommand(name: 'oib:configure', description: 'Sets target entities, generates and applies migrations, implements interfaces')]
class ConfigureCommand extends Command
{
    private QuestionHelper $qHelper;

    public function __construct
    (
        private readonly string $projectDir,
        private readonly Filesystem $filesystem,
    )
    {
        parent::__construct();

        $this->qHelper = new QuestionHelper();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $process = new Process(['git', 'update-index', '--refresh']);
        $process->run();
        $process = new Process(['git', 'diff-index', '--quiet', 'HEAD', '--']);
        $process->run();

        if (!$process->isSuccessful())
        {
            $output->writeln('<error>You have uncommitted changes. Please commit them first.</error>');
            return Command::FAILURE;
        }

        $getEntitiesFromInputResult = $this->getEntitiesFromInput($input, $output);
        if (is_int($getEntitiesFromInputResult)) {return $getEntitiesFromInputResult;}

        $updateResolveTargetEntitiesResult = $this->updateResolveTargetEntities($output, $getEntitiesFromInputResult);
        if (is_int($updateResolveTargetEntitiesResult)) {return $updateResolveTargetEntitiesResult;}

        $generateMigrationsResult = $this->generateMigrations($output);
        if (is_int($generateMigrationsResult)) {return $generateMigrationsResult;}

        $applyMigrationsResult = $this->applyMigrations($output);
        if (is_int($applyMigrationsResult)) {return $applyMigrationsResult;}

        $implementInterfaceRes = $this->implementInterface($output, $getEntitiesFromInputResult['customerEntAbsPath'], 'Psys\OrderInvoiceBundle\Model\CustomerInterface as OIBCustomerInterface', 'OIBCustomerInterface');
        if (is_int($implementInterfaceRes)) {return $implementInterfaceRes;}

        $implementInterfaceRes = $this->implementInterface($output, $getEntitiesFromInputResult['fileEntAbsPath'], 'Psys\OrderInvoiceBundle\Model\FileInterface as OIBFileInterface', 'OIBFileInterface');
        if (is_int($implementInterfaceRes)) {return $implementInterfaceRes;}

        $output->writeln('<info>✅ Order Invoice installation complete!</info>');
        return Command::SUCCESS;
    }

    private function getEntitiesFromInput(InputInterface $input, OutputInterface $output): int|array
    {
        $customerEntFQCN = $this->qHelper->ask($input, $output, new Question('Entity which owns the order (default: App\Entity\User): ', 'App\Entity\User'));

        try 
        {
            $refCustomer = new \ReflectionClass($customerEntFQCN);
        } 
        catch (\ReflectionException $e)
        {
            $output->writeln("<error>The file '".$customerEntFQCN."' does not exist</error>");
            return Command::FAILURE;
        }

        $fileEntFQCN = $this->qHelper->ask($input, $output, new Question('Entity describing invoice PDF file saved to disk (default: App\Entity\File): ', 'App\Entity\File'));
        try 
        {
            $refFile = new \ReflectionClass($fileEntFQCN);
        } 
        catch (\ReflectionException $e)
        {
            $output->writeln("<error>The file '".$fileEntFQCN."' does not exist</error>");
            return Command::FAILURE;
        }

        return [
            'customerEntFQCN' => $customerEntFQCN,
            'fileEntFQCN' => $fileEntFQCN,
            'customerEntAbsPath' => $refCustomer->getFileName(),
            'fileEntAbsPath' => $refFile->getFileName(),
        ];
    }

    private function updateResolveTargetEntities(OutputInterface $output, array $getEntitiesFromInputResult): int|bool
    {
        $doctrineYaml = 'config/packages/doctrine.yaml';
        $doctrineYamlAbs = $this->projectDir.'/'.$doctrineYaml;
        if (!$this->filesystem->exists($doctrineYamlAbs))
        {
            $output->writeln("<error>The file '".$doctrineYaml."' does not exist</error>");
            return Command::FAILURE;
        }

        // Update doctrine.yaml resolve_target_entities
        $data = Yaml::parseFile($doctrineYamlAbs);
        $rte = &$data['doctrine']['orm']['resolve_target_entities'];
        $add = [
            'Psys\OrderInvoiceBundle\Model\CustomerInterface' => $getEntitiesFromInputResult['customerEntFQCN'],
            'Psys\OrderInvoiceBundle\Model\FileInterface' => $getEntitiesFromInputResult['fileEntFQCN'],
        ];
        $changed = false;
        foreach ($add as $k => $v) 
        {
            if (!isset($rte[$k]) || $rte[$k] !== $v) 
            {
                $rte[$k] = $v;
                $changed = true;
            }
        }
        if ($changed) 
        {
            file_put_contents($doctrineYamlAbs, Yaml::dump($data, 6));
            $output->writeln('<info>Updated config/packages/doctrine.yaml</info>');
        }

        return true;
    }

    private function generateMigrations(OutputInterface $output): bool|int
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

        
        // Generate init migration
        $migrationsDir = $this->projectDir.'/migrations';
        $finder = new Finder();
        $finder->files()->in($migrationsDir)->sortByChangedTime()->reverseSorting();  
        $finderArr = iterator_to_array($finder);
        $latestMigration = $finderArr[array_key_first($finderArr)];
        $latestMigrationDateStr = u($latestMigration)->match('/Version(\d+)/')[1];
        $DTI_latestMigration = new \DateTimeImmutable($latestMigrationDateStr);
        $DTI_newMigration = $DTI_latestMigration->modify('+1 second');
        $newMigrationName = 'Version'.$DTI_newMigration->format('YmdHis');
        
        $output->writeln('Generating migration to initialize the database...');
        $initDbMigrationProcess = new Process(['bin/console', 'make:oib:init_database', $newMigrationName]);
        $initDbMigrationProcess->run();
        if (!$initDbMigrationProcess->isSuccessful()) 
        {
            $output->writeln('<error>Failed to generate migration:</error>');
            $output->writeln($initDbMigrationProcess->getErrorOutput());
            return Command::FAILURE;
        }

        $this->filesystem->rename(
            $this->projectDir.'/src/'.$newMigrationName.'.php',
            $migrationsDir.'/'.$newMigrationName.'.php',
            true
        );

        $output->writeln('<info>Init migration generated!</info>');

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

    private function implementInterface(OutputInterface $output, string $fileAbsPath, string $interfaceUseName, string $interfaceClassName): int|bool
    {
        $code = file_get_contents($fileAbsPath);

        // Add use statement if missing
        if (strpos($code, "use $interfaceUseName;") === false) 
        {
            // insert after namespace declaration
            $code = preg_replace(
                '/^namespace\s+[^;]+;/m',
                "$0\n\nuse $interfaceUseName;",
                $code
            );
        }
        
        // Add interface in class declaration if missing
        $code = preg_replace_callback('/class\s+(\w+)\s*(?:extends\s+(\w+))?\s*(?:implements\s+([^{]+))?/',
            function ($m) use ($interfaceClassName, $output) 
            {
                $className = $m[1];
                $list = !empty($m[3]) ? array_map('trim', explode(',', $m[3])) : [];
                
                if (!in_array($interfaceClassName, $list)) // the interface is not present
                {
                    $list[] = $interfaceClassName;
                    $newImplements = ' implements ' . implode(', ', $list);
                } 
                else // nothing to do
                {
                    return $m[0];
                }

                return 'class ' . $className . (!empty($m[2]) ? ' extends '.$m[2] : '') . $newImplements . PHP_EOL;
            },
            $code
        );

        file_put_contents($fileAbsPath, $code);
        $output->writeln('<info>Interface added to '. str_replace($this->projectDir.'/', '', $fileAbsPath) .'</info>');

        return true;
    }
}
