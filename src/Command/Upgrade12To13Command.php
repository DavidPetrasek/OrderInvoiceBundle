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
use Symfony\Component\Process\Process;


#[AsCommand(name: 'oib:upgrade:12_to_13', description: 'Upgrades OrderInvoiceBundle from version 1.2 to 1.3')]
class Upgrade12To13Command extends Command
{
    private QuestionHelper $qHelper;
    private const FILE_ENTITY_FQCN_DEFAULT = 'Psys\OrderInvoiceBundle\Entity\File';

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

        $output->writeln(PHP_EOL."<info>During this process, you can either continue using your current (custom) file entity or switch to the new default file entity. If you decide to keep using your current (custom) file entity, you don't have to change anything. If you choose to switch to the new file entity (table `oi_file`), your current file records will not be automatically transferred to the new table `oi_file`.</info>".PHP_EOL);

        $getEntitiesFromInputResult = $this->getEntitiesFromInput($input, $output);
        if (is_int($getEntitiesFromInputResult)) {return $getEntitiesFromInputResult;}

        $updateResolveTargetEntitiesResult = $this->updateResolveTargetEntities($output, $getEntitiesFromInputResult);
        if (is_int($updateResolveTargetEntitiesResult)) {return $updateResolveTargetEntitiesResult;}

        $generateMigrationsResult = $this->generateMigrations($output);
        if (is_int($generateMigrationsResult)) {return $generateMigrationsResult;}

        if ($getEntitiesFromInputResult['fileEntFQCN'] !== self::FILE_ENTITY_FQCN_DEFAULT)
        {
            $implementInterfaceRes = $this->implementInterface($output, $getEntitiesFromInputResult['fileEntAbsPath'], 'Psys\OrderInvoiceBundle\Model\FileInterface as OIBFileInterface', 'OIBFileInterface');
            if (is_int($implementInterfaceRes)) {return $implementInterfaceRes;}
        }
        
        $generateConfigResult = $this->generateConfig($input, $output, $getEntitiesFromInputResult['fileEntFQCN']);
        if (is_int($generateConfigResult)) {return $generateConfigResult;}

        $output->writeln(PHP_EOL.'<info>✅ Upgrade complete!</info>');
        $output->writeln(PHP_EOL."<info>Next steps:</info>" 
        .PHP_EOL."<info>1) Review the new migration and apply it by:</info> <comment>symfony console doctrine:migrations:migrate</comment>"
        .PHP_EOL."<info>2) If you wish to use the new FilePersister/FileDeleter, make sure all chosen directories exist.</info>");
        return Command::SUCCESS;
    }

    private function getEntitiesFromInput(InputInterface $input, OutputInterface $output): int|array
    {
        $fileEntFQCN = $this->qHelper->ask($input, $output, new Question(PHP_EOL.'Entity describing invoice file saved to disk (defaults to '.self::FILE_ENTITY_FQCN_DEFAULT.'): ', self::FILE_ENTITY_FQCN_DEFAULT));
        
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
            'fileEntFQCN' => $fileEntFQCN,
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

        $data = Yaml::parseFile($doctrineYamlAbs);
        $rte = &$data['doctrine']['orm']['resolve_target_entities'];
        $add = [
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

    private function generateConfig(InputInterface $input, OutputInterface $output, string $fileEntFQCN): bool|int
    {
        $storagePathProforma = $this->qHelper->ask($input, $output, new Question(
            PHP_EOL.'Proforma invoice storage directory (defaults to /var/data/invoice/proforma):',
           '/var/data/invoice/proforma'
        ));
        $storagePathFinal = $this->qHelper->ask($input, $output, new Question(
            PHP_EOL.'Final invoice storage directory (defaults to /var/data/invoice/final):',
            '/var/data/invoice/final'
        ));

        $yamlAbs = $this->projectDir.'/config/packages/psys_order_invoice.yaml';
        $data = 
        [
            'psys_order_invoice' =>
            [
                'file_entity' => $fileEntFQCN,
                'storage_path' => 
                [
                    'proforma' => $storagePathProforma,
                    'final' => $storagePathFinal,
                ]
            ]
        ];
       
        file_put_contents($yamlAbs, Yaml::dump($data, 6));
        $output->writeln('<info>Created config/packages/psys_order_invoice.yaml</info>');

        return true;
    }
}
