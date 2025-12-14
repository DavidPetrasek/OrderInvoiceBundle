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


#[AsCommand(name: 'oib:styler:enable', description: 'Enables the Styler')]
class StylerEnableCommand extends Command
{
    private QuestionHelper $qHelper;

    public function __construct
    (
        private readonly string $projectDir,
        private readonly Filesystem $filesystem
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

        $updateConfigResult = $this->updateConfig($output, $getEntitiesFromInputResult);
        if (is_int($updateConfigResult)) {return $updateConfigResult;}
        
        $generateConfigResult = $this->generateRouteConfig($output);
        if (is_int($generateConfigResult)) {return $generateConfigResult;}

        $output->writeln(PHP_EOL.'<info>✅ Styler enabled!</info>');
        return Command::SUCCESS;
    }

    private function getEntitiesFromInput(InputInterface $input, OutputInterface $output): int|array
    {
        $invoiceBinaryProviderFQCN = $this->qHelper->ask($input, $output, new Question(PHP_EOL.'Invoice binary provider FQCN (default: App\Service\OrderInvoiceBundle\InvoiceBinaryProvider):', 'App\Service\OrderInvoiceBundle\InvoiceBinaryProvider'));

        try 
        {
            $refInvoiceBinaryProvider = new \ReflectionClass($invoiceBinaryProviderFQCN);
        } 
        catch (\ReflectionException $e)
        {
            $output->writeln("<error>The file '".$invoiceBinaryProviderFQCN."' does not exist</error>");
            return Command::FAILURE;
        }

        return [
            'invoiceBinaryProviderFQCN' => $invoiceBinaryProviderFQCN,
            'invoiceBinaryProviderAbsPath' => $refInvoiceBinaryProvider->getFileName(),
        ];
    }

    private function updateConfig(OutputInterface $output, array $getEntitiesFromInputResult): int|bool
    {
        $cfgYaml = 'config/packages/psys_order_invoice.yaml';
        $cfgYamlAbs = $this->projectDir.'/'.$cfgYaml;
        if (!$this->filesystem->exists($cfgYamlAbs))
        {
            $output->writeln("<error>The file '".$cfgYaml."' does not exist</error>");
            return Command::FAILURE;
        }

        $data = Yaml::parseFile($cfgYamlAbs);
        $data['psys_order_invoice']['invoice_binary_provider'] =  $getEntitiesFromInputResult['invoiceBinaryProviderFQCN'];
     
        file_put_contents($cfgYamlAbs, Yaml::dump($data, 6));
        $output->writeln('<info>Updated config/packages/psys_order_invoice.yaml</info>');

        return true;
    }

    private function generateRouteConfig(OutputInterface $output): bool|int
    {
        $yamlAbs = $this->projectDir.'/config/routes/psys_order_invoice.yaml';
        $data = 
        [
            'when@dev' =>
            [
                'psys_order_invoice' =>
                [
                    'resource' => 
                    [
                        'path' => '@PsysOrderInvoiceBundle/src/Controller/Dev/',
                        'namespace' => 'Psys\OrderInvoiceBundle\Controller\Dev',
                    ],
                    'type' => 'attribute'
                ]
            ]
        ];
       
        file_put_contents($yamlAbs, Yaml::dump($data, 6));
        $output->writeln('<info>Created config/routes/psys_order_invoice.yaml</info>');

        return true;
    }
}
