<?php
namespace Psys\OrderInvoiceBundle\Service\FilePersister;

use Doctrine\ORM\EntityManagerInterface;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;
use Psys\OrderInvoiceBundle\Service\FileDeleter\FileDeleter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypes;


class FilePersister
{
    private const FILE_ENTITY_FQCN_DEFAULT = 'Psys\OrderInvoiceBundle\Entity\File';

    public function __construct
    (
        private readonly Filesystem $filesystem,
        private readonly EntityManagerInterface $em,
        private readonly FileDeleter $fileDeleter,
        private readonly string $projectDir,
        private readonly string $fileEntityFQCN, // Yaml config
        private readonly array $storagePath // Yaml config
    )
    {}

    /**
      * Persists the proforma file binary to disk and saves a reference to it in the database using the default File entity.
      * If a custom File entity is used, only saves the file to disk and returns the file info for further processing.
     */
    public function persistProforma(string $binary, Order $ent_Order): ?array
    {
        return $this->persist($binary, $ent_Order, InvoiceType::PROFORMA);
    }

    /**
      * Persists the final invoice file binary to disk and saves a reference to it in the database using the default File entity.
      * If a custom File entity is used, only saves the file to disk and returns the file info for further processing.
      *
      * @return array|null file info array properties: mimeType, nameFileSystem, nameDisplay
     */
    public function persistFinal(string $binary, Order $ent_Order): ?array
    {
        return $this->persist($binary, $ent_Order, InvoiceType::FINAL);
    }

    /**
      * Persists the file binary to disk and saves a reference to it in the database using the default File entity.
      * If a custom File entity is used, only saves the file to disk and returns the file info for further processing.
      *
      * @return array|null file info array properties: mimeType, nameFileSystem, nameDisplay
     */
    private function persist(string $binary, Order $ent_Order, InvoiceType $invoiceType): ?array
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

        // Default File entity is being used
        if ($this->fileEntityFQCN === self::FILE_ENTITY_FQCN_DEFAULT)
        {
            // Remove existing file to avoid orphaned files
            if ($invoiceType === InvoiceType::PROFORMA)
            {
                $this->fileDeleter->deleteProforma($ent_Order);
            }
            else if ($invoiceType === InvoiceType::FINAL)
            {
                $this->fileDeleter->deleteFinal($ent_Order);
            }
        }

        // Save to disk
        $absPath = $this->filesystem->tempnam($this->projectDir.$storagePath, '', '.'.$extension); 
        $this->filesystem->appendToFile($absPath, $binary);
        $nameFileSystem = basename($absPath);
        
        // Save reference to the file in the database using the default File entity
        if ($this->fileEntityFQCN === self::FILE_ENTITY_FQCN_DEFAULT)
        {
            $ent_File = (new $this->fileEntityFQCN())
                ->setMimeType($mimeType)
                ->setNameFileSystem($nameFileSystem)
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
            return null;
        }
        // If using a custom File entity, return the file info instead
        else
        {
            return 
            [
                'mimeType' => $mimeType,
                'nameFileSystem' => $nameFileSystem,
                'nameDisplay' => $nameDisplay,
            ];
        }
    }
}