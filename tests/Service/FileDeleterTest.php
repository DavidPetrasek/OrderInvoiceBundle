<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\File;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceRegular;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Service\FileDeleter\FileDeleter;
use Symfony\Component\Filesystem\Filesystem;

class FileDeleterTest extends TestCase
{
    private MockObject $filesystem;
    private MockObject $em;
    private FileDeleter $fileDeleter;
    private string $projectDir = '/app';
    private array $storagePath;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->em = $this->createMock(EntityManagerInterface::class);

        $this->storagePath = [
            'proforma' => '/storage/invoices/proforma',
            'advance' => '/storage/invoices/advance',
            'final' => '/storage/invoices/final',
            'regular' => '/storage/invoices/regular',
        ];

        $this->fileDeleter = new FileDeleter(
            $this->filesystem,
            $this->em,
            $this->projectDir,
            $this->storagePath
        );
    }

    public function testDeleteProformaInvoiceFile(): void
    {
        $order = new Order();
        $invoice = new InvoiceProforma();
        $file = new File();
        $file->setNameFileSystem('proforma_12345.pdf');

        $invoice->setFile($file);
        $order->setInvoiceProforma($invoice);

        $this->filesystem
            ->expects($this->once())
            ->method('remove')
            ->with($this->projectDir . $this->storagePath['proforma'] . '/proforma_12345.pdf');

        $this->em->expects($this->atLeastOnce())->method('persist');
        $this->em->expects($this->once())->method('remove')->with($file);
        $this->em->expects($this->once())->method('flush');

        $this->fileDeleter->deleteProforma($order);
    }

    public function testDeleteProformaInvoiceWithCustomFileName(): void
    {
        $order = new Order();
        $invoice = new InvoiceProforma();
        $file = new File();
        $file->setNameFileSystem('proforma_original.pdf');

        $invoice->setFile($file);
        $order->setInvoiceProforma($invoice);

        $customFileName = 'custom_proforma_file.pdf';

        $this->filesystem
            ->expects($this->once())
            ->method('remove')
            ->with($this->projectDir . $this->storagePath['proforma'] . '/' . $customFileName);

        $this->em->expects($this->atLeastOnce())->method('persist');
        $this->em->expects($this->once())->method('remove')->with($file);
        $this->em->expects($this->once())->method('flush');

        $this->fileDeleter->deleteProforma($order, $customFileName);
    }

    public function testDeleteAdvanceInvoiceFile(): void
    {
        $order = new Order();
        $advance = new InvoiceAdvance();
        $file = new File();
        $file->setNameFileSystem('advance_99999.pdf');

        $advance->setFile($file);
        $advance->setOrder($order);

        $this->filesystem
            ->expects($this->once())
            ->method('remove')
            ->with($this->projectDir . $this->storagePath['advance'] . '/advance_99999.pdf');

        $this->em->expects($this->atLeastOnce())->method('persist');
        $this->em->expects($this->once())->method('remove')->with($file);
        $this->em->expects($this->once())->method('flush');

        $this->fileDeleter->deleteAdvance($advance);
    }

    public function testDeleteFinalInvoiceFile(): void
    {
        $order = new Order();
        $invoice = new InvoiceFinal();
        $file = new File();
        $file->setNameFileSystem('final_55555.pdf');

        $invoice->setFile($file);
        $order->setInvoiceFinal($invoice);

        $this->filesystem
            ->expects($this->once())
            ->method('remove')
            ->with($this->projectDir . $this->storagePath['final'] . '/final_55555.pdf');

        $this->em->expects($this->atLeastOnce())->method('persist');
        $this->em->expects($this->once())->method('remove')->with($file);
        $this->em->expects($this->once())->method('flush');

        $this->fileDeleter->deleteFinal($order);
    }

    public function testDeleteRegularInvoiceFile(): void
    {
        $order = new Order();
        $invoice = new InvoiceRegular();
        $file = new File();
        $file->setNameFileSystem('regular_77777.pdf');

        $invoice->setFile($file);
        $order->setInvoiceRegular($invoice);

        $this->filesystem
            ->expects($this->once())
            ->method('remove')
            ->with($this->projectDir . $this->storagePath['regular'] . '/regular_77777.pdf');

        $this->em->expects($this->atLeastOnce())->method('persist');
        $this->em->expects($this->once())->method('remove')->with($file);
        $this->em->expects($this->once())->method('flush');

        $this->fileDeleter->deleteRegular($order);
    }

    public function testDeleteProformaWithoutFileDoesNotThrow(): void
    {
        $order = new Order();
        $invoice = new InvoiceProforma();
        $invoice->setFile(null);

        $order->setInvoiceProforma($invoice);

        $this->filesystem->expects($this->never())->method('remove');
        $this->em->expects($this->never())->method('remove');
        $this->em->expects($this->never())->method('flush');

        $this->fileDeleter->deleteProforma($order);
    }

    public function testDeleteAdvanceWithoutFileDoesNotThrow(): void
    {
        $order = new Order();
        $advance = new InvoiceAdvance();
        $advance->setOrder($order);
        $advance->setFile(null);

        $this->filesystem->expects($this->never())->method('remove');
        $this->em->expects($this->never())->method('remove');
        $this->em->expects($this->never())->method('flush');

        $this->fileDeleter->deleteAdvance($advance);
    }

    public function testDeleteFinalWithoutFileDoesNotThrow(): void
    {
        $order = new Order();
        $invoice = new InvoiceFinal();
        $invoice->setFile(null);

        $order->setInvoiceFinal($invoice);

        $this->filesystem->expects($this->never())->method('remove');
        $this->em->expects($this->never())->method('remove');
        $this->em->expects($this->never())->method('flush');

        $this->fileDeleter->deleteFinal($order);
    }

    public function testDeleteRegularWithoutFileDoesNotThrow(): void
    {
        $order = new Order();
        $invoice = new InvoiceRegular();
        $invoice->setFile(null);

        $order->setInvoiceRegular($invoice);

        $this->filesystem->expects($this->never())->method('remove');
        $this->em->expects($this->never())->method('remove');
        $this->em->expects($this->never())->method('flush');

        $this->fileDeleter->deleteRegular($order);
    }

    public function testDeleteProformaRemovedFromInvoiceAfterDelete(): void
    {
        $order = new Order();
        $invoice = new InvoiceProforma();
        $file = new File();
        $file->setNameFileSystem('test.pdf');

        $invoice->setFile($file);
        $order->setInvoiceProforma($invoice);

        $this->filesystem->expects($this->once())->method('remove');
        $this->em->expects($this->atLeastOnce())->method('persist')->willReturnCallback(
            function ($entity) use ($invoice): void {
                if ($entity === $invoice) {
                    // After deletion, file should be null
                    $this->assertNull($invoice->getFile());
                }
            }
        );
        $this->em->expects($this->once())->method('remove')->with($file);
        $this->em->expects($this->once())->method('flush');

        $this->fileDeleter->deleteProforma($order);
    }

    public function testMultipleFilesCanBeDeleted(): void
    {
        $order = new Order();

        // Proforma invoice with file
        $proforma = new InvoiceProforma();
        $proformaFile = new File();
        $proformaFile->setNameFileSystem('proforma.pdf');

        $proforma->setFile($proformaFile);
        $order->setInvoiceProforma($proforma);

        // Final invoice with file
        $final = new InvoiceFinal();
        $finalFile = new File();
        $finalFile->setNameFileSystem('final.pdf');

        $final->setFile($finalFile);
        $order->setInvoiceFinal($final);

        $this->filesystem->expects($this->exactly(2))->method('remove');
        $this->em->expects($this->atLeast(2))->method('persist');
        $this->em->expects($this->exactly(2))->method('remove');
        $this->em->expects($this->exactly(2))->method('flush');

        $this->fileDeleter->deleteProforma($order);
        $this->fileDeleter->deleteFinal($order);
    }
}
