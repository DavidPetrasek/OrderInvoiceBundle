<?php
namespace Psys\OrderInvoiceBundle\Service\FilePersister;

use Doctrine\ORM\EntityManagerInterface;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Mime\MimeTypes;


class FilePersister
{
    public function __construct
    (
        private readonly Filesystem $filesystem,
        private readonly EntityManagerInterface $em,
        private readonly string $projectDir,
        private readonly string $fileEntityFQCN, // Yaml config
        private readonly string $storagePath // Yaml config
    )
    {}

    public function persistProforma(string $binary, Order $ent_Order): void
    {
        $this->persist($binary, $ent_Order, InvoiceType::PROFORMA);
    }

    public function persistFinal(string $binary, Order $ent_Order): void
    {
        $this->persist($binary, $ent_Order, InvoiceType::FINAL);
    }

    private function persist(string $binary, Order $ent_Order, InvoiceType $invoiceType): void
    {
        // Guess MIME Type and file extension from binary data       
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($binary);
        $mimeTypes = new MimeTypes();
        $extensions = $mimeTypes->getExtensions($mimeType);
        $extension = $extensions[0];
        
        if ($invoiceType === InvoiceType::PROFORMA)
        {
            $storagePath = $this->storagePath['proforma'];
            $nameDisplay = 'proforma_invoice.'.$extension;
        }
        else if ($invoiceType === InvoiceType::FINAL)
        {
            $storagePath = $this->storagePath['final'];
            $nameDisplay = 'final_invoice.'.$extension;
        }

        // Save to disk
        $absPath = $this->filesystem->tempnam($this->projectDir.$storagePath, '', $extension); 
        $this->filesystem->appendToFile($absPath, $binary);
        
        // Save reference to the file in the database
        $file = new File($absPath);
        $mimeType = $file->getMimeType();

        $ent_File = (new $this->fileEntityFQCN())
            ->setMimeType($mimeType)
            ->setNameFileSystem(basename($absPath))
            ->setNameDisplay($nameDisplay)
            ->setCreatedAt();

        if ($invoiceType === InvoiceType::PROFORMA)
        {
            $ent_InvoiceProforma = $ent_Order->getInvoice()->getInvoiceProforma();
            $ent_InvoiceProforma->setFile($ent_File);
            $this->em->persist($ent_InvoiceProforma);
        }
        else if ($invoiceType === InvoiceType::FINAL)
        {
            $ent_InvoiceFinal = $ent_Order->getInvoice()->getInvoiceFinal();
            $ent_InvoiceFinal->setFile($ent_File);
            $this->em->persist($ent_InvoiceFinal);
        }
        
        $this->em->flush();
    }
}