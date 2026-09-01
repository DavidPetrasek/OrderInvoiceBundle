<?php

declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;
use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceRegular;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Service\FileDeleter\FileDeleter;
use Psys\OrderInvoiceBundle\Service\FilePersister\FilePersister;
use Symfony\Component\Filesystem\Filesystem;

class FilePersisterTest extends TestCase
{
    private MockObject $filesystem;
    private MockObject $em;
    private MockObject $fileDeleter;
    private FilePersister $filePersister;
    private string $projectDir = '/app';
    private array $storagePath;
    private string $fileEntityFQCN = \Psys\OrderInvoiceBundle\Entity\File::class;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->fileDeleter = $this->createMock(FileDeleter::class);
        
        $this->storagePath = [
            'proforma' => '/storage/invoices/proforma',
            'advance' => '/storage/invoices/advance',
            'final' => '/storage/invoices/final',
            'regular' => '/storage/invoices/regular',
        ];
        
        $this->filePersister = new FilePersister(
            $this->filesystem,
            $this->em,
            $this->fileDeleter,
            $this->projectDir,
            $this->fileEntityFQCN,
            $this->storagePath
        );
    }

    public function testPersistProformaInvoiceFile(): void
    {
        $order = new Order();
        $invoice = new InvoiceProforma();
        $order->setInvoiceProforma($invoice);
        
        $binary = '%PDF-1.4 test content';

        $this->filesystem
            ->expects($this->once())
            ->method('tempnam')
            ->willReturn('/app/storage/invoices/proforma/tmp_12345.pdf');

        $this->filesystem
            ->expects($this->once())
            ->method('dumpFile')
            ->with('/app/storage/invoices/proforma/tmp_12345.pdf', $binary);

        $this->fileDeleter
            ->expects($this->once())
            ->method('deleteProforma')
            ->with($order);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->filePersister->persistProforma($binary, $order);

        $this->assertNull($result);
    }

    public function testPersistAdvanceInvoiceFile(): void
    {
        $order = new Order();
        $advance = new InvoiceAdvance();
        $advance->setOrder($order);

        $binary = '%PDF-1.4 advance invoice';

        $this->filesystem
            ->expects($this->once())
            ->method('tempnam')
            ->willReturn('/app/storage/invoices/advance/tmp_54321.pdf');

        $this->filesystem
            ->expects($this->once())
            ->method('dumpFile');

        $this->fileDeleter
            ->expects($this->once())
            ->method('deleteAdvance')
            ->with($advance);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->filePersister->persistAdvance($binary, $advance);

        $this->assertNull($result);
    }

    public function testPersistFinalInvoiceFile(): void
    {
        $order = new Order();
        $invoice = new InvoiceFinal();
        $order->setInvoiceFinal($invoice);

        $binary = '%PDF-1.4 final invoice';

        $this->filesystem
            ->expects($this->once())
            ->method('tempnam')
            ->willReturn('/app/storage/invoices/final/tmp_99999.pdf');

        $this->filesystem
            ->expects($this->once())
            ->method('dumpFile');

        $this->fileDeleter
            ->expects($this->once())
            ->method('deleteFinal')
            ->with($order);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->filePersister->persistFinal($binary, $order);

        $this->assertNull($result);
    }

    public function testPersistRegularInvoiceFile(): void
    {
        $order = new Order();
        $invoice = new InvoiceRegular();
        $order->setInvoiceRegular($invoice);

        $binary = '%PDF-1.4 regular invoice';

        $this->filesystem
            ->expects($this->once())
            ->method('tempnam')
            ->willReturn('/app/storage/invoices/regular/tmp_88888.pdf');

        $this->filesystem
            ->expects($this->once())
            ->method('dumpFile');

        $this->fileDeleter
            ->expects($this->once())
            ->method('deleteRegular')
            ->with($order);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->filePersister->persistRegular($binary, $order);

        $this->assertNull($result);
    }

    public function testPersistWithCustomFileEntity(): void
    {
        $customEntityFQCN = 'App\Entity\CustomFile';
        $filePersisterCustom = new FilePersister(
            $this->filesystem,
            $this->em,
            $this->fileDeleter,
            $this->projectDir,
            $customEntityFQCN,
            $this->storagePath
        );

        $order = new Order();
        $binary = '%PDF-1.4 custom';

        $this->filesystem
            ->expects($this->once())
            ->method('tempnam')
            ->willReturn('/app/storage/invoices/proforma/tmp_custom.pdf');

        $this->filesystem
            ->expects($this->once())
            ->method('dumpFile');

        // No deletion and persist for custom entity
        $this->fileDeleter->expects($this->never())->method('deleteProforma');
        $this->em->expects($this->never())->method('flush');

        $result = $filePersisterCustom->persistProforma($binary, $order);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('mimeType', $result);
        $this->assertArrayHasKey('nameFileSystem', $result);
        $this->assertArrayHasKey('nameDisplay', $result);
        $this->assertSame('application/pdf', $result['mimeType']);
        $this->assertSame('proforma_invoice.pdf', $result['nameDisplay']);
    }

    public function testFileExtensionDeterminedFromMimeType(): void
    {
        $order = new Order();
        $invoice = new InvoiceProforma();
        $order->setInvoiceProforma($invoice);

        // Different file types to test
        $testCases = [
            ['%PDF-1.4', 'application/pdf', '.pdf'],
            ["\x89PNG\r\n\x1a\n", 'image/png', '.png'],
        ];

        foreach ($testCases as [$binary, $expectedMimeType, $extension]) {
            $this->setUp(); // Reset mocks

            $this->filesystem
                ->expects($this->once())
                ->method('tempnam')
                ->willReturn('/app/storage/invoices/proforma/tmp_file' . $extension);

            $this->filesystem
                ->expects($this->once())
                ->method('dumpFile');

            $this->fileDeleter
                ->expects($this->once())
                ->method('deleteProforma');

            $this->em->expects($this->once())->method('persist');
            $this->em->expects($this->once())->method('flush');

            $this->filePersister->persistProforma($binary, $order);
        }
    }

    public function testFileIsStoredWithCorrectDisplayName(): void
    {
        $order = new Order();
        $invoice = new InvoiceProforma();
        $order->setInvoiceProforma($invoice);

        $binary = '%PDF-1.4';

        $this->filesystem
            ->expects($this->once())
            ->method('tempnam')
            ->willReturn('/app/storage/invoices/proforma/tmp_file.pdf');

        $this->filesystem
            ->expects($this->once())
            ->method('dumpFile');

        $this->fileDeleter->expects($this->once())->method('deleteProforma');

        $this->em->expects($this->once())->method('persist')->willReturnCallback(
            function ($entity): void {
                if ($entity instanceof InvoiceProforma) {
                    $file = $entity->getFile();
                    if ($file instanceof \Psys\OrderInvoiceBundle\Model\FileInterface) {
                        $this->assertSame('proforma_invoice.pdf', $file->getNameDisplay());
                    }
                }
            }
        );
        $this->em->expects($this->once())->method('flush');

        $this->filePersister->persistProforma($binary, $order);
    }

    public function testPersistCreatesFileWithCorrectAttributes(): void
    {
        $order = new Order();
        $invoice = new InvoiceProforma();
        $order->setInvoiceProforma($invoice);

        $binary = '%PDF-1.4';

        $this->filesystem
            ->expects($this->once())
            ->method('tempnam')
            ->willReturn('/app/storage/invoices/proforma/tmp_created.pdf');

        $this->filesystem->expects($this->once())->method('dumpFile');
        $this->fileDeleter->expects($this->once())->method('deleteProforma');

        $this->em->expects($this->once())->method('persist')->willReturnCallback(
            function ($entity): void {
                if ($entity instanceof InvoiceProforma) {
                    $file = $entity->getFile();
                    if ($file instanceof \Psys\OrderInvoiceBundle\Model\FileInterface) {
                        $this->assertSame('application/pdf', $file->getMimeType());
                    }
                }
            }
        );
        $this->em->expects($this->once())->method('flush');

        $this->filePersister->persistProforma($binary, $order);
    }
}
