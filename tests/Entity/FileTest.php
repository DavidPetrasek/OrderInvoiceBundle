<?php

namespace Psys\OrderInvoiceBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\File;

class FileTest extends TestCase
{
    public function testFileInitialization(): void
    {
        $file = new File();

        $this->assertNull($file->getId());
        $this->assertNull($file->getMimeType());
        $this->assertNull($file->getNameFileSystem());
        $this->assertNull($file->getNameDisplay());
        $this->assertNull($file->getCreatedAt());
    }

    public function testSetAndGetMimeType(): void
    {
        $file = new File();
        $mimeType = 'application/pdf';

        $result = $file->setMimeType($mimeType);

        $this->assertSame($file, $result);
        $this->assertSame($mimeType, $file->getMimeType());
    }

    public function testSetAndGetNameFileSystem(): void
    {
        $file = new File();
        $name = 'invoice_12345.pdf';

        $result = $file->setNameFileSystem($name);

        $this->assertSame($file, $result);
        $this->assertSame($name, $file->getNameFileSystem());
    }

    public function testSetAndGetNameDisplay(): void
    {
        $file = new File();
        $displayName = 'Invoice_2024.pdf';

        $result = $file->setNameDisplay($displayName);

        $this->assertSame($file, $result);
        $this->assertSame($displayName, $file->getNameDisplay());
    }

    public function testSetCreatedAtWithCurrentTime(): void
    {
        $file = new File();
        $before = new \DateTimeImmutable();

        $result = $file->setCreatedAt(new \DateTimeImmutable());

        $after = new \DateTimeImmutable();

        $this->assertSame($file, $result);
        $this->assertInstanceOf(\DateTimeImmutable::class, $file->getCreatedAt());
        $this->assertGreaterThanOrEqual($before, $file->getCreatedAt());
        $this->assertLessThanOrEqual($after, $file->getCreatedAt());
    }

    public function testSetCreatedAtWithSpecificTime(): void
    {
        $file = new File();
        $specificTime = new \DateTimeImmutable('2024-01-15 10:30:45');

        $result = $file->setCreatedAt($specificTime);

        $this->assertSame($file, $result);
        $this->assertSame($specificTime, $file->getCreatedAt());
    }

    public function testSetCreatedAtWithNull(): void
    {
        $file = new File();

        $result = $file->setCreatedAt(null);

        $this->assertSame($file, $result);
        $this->assertSame(null, $file->getCreatedAt());
    }

    public function testCompleteFileDataFlow(): void
    {
        $file = new File();
        $createdAt = new \DateTimeImmutable('2024-02-20 14:50:30');

        $file
            ->setMimeType('application/pdf')
            ->setNameFileSystem('file_temp_12345.pdf')
            ->setNameDisplay('ProformaInvoice_2024-02.pdf')
            ->setCreatedAt($createdAt);

        $this->assertSame('application/pdf', $file->getMimeType());
        $this->assertSame('file_temp_12345.pdf', $file->getNameFileSystem());
        $this->assertSame('ProformaInvoice_2024-02.pdf', $file->getNameDisplay());
        $this->assertSame($createdAt, $file->getCreatedAt());
    }

    public function testDifferentMimeTypes(): void
    {
        $mimeTypes = [
            'application/pdf',
            'application/vnd.ms-excel',
            'image/png',
            'text/plain',
            'application/zip',
        ];

        foreach ($mimeTypes as $mimeType) {
            $file = new File();
            $file->setMimeType($mimeType);
            $this->assertSame($mimeType, $file->getMimeType());
        }
    }

    public function testChainedSetters(): void
    {
        $file = new File();
        $createdAt = new \DateTimeImmutable();

        $result = $file
            ->setMimeType('application/pdf')
            ->setNameFileSystem('invoice.pdf')
            ->setNameDisplay('My Invoice.pdf')
            ->setCreatedAt($createdAt);

        $this->assertSame($file, $result);
    }
}
