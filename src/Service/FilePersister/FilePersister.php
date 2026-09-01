<?php

declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Service\FilePersister;

use Doctrine\ORM\EntityManagerInterface;
use Psys\OrderInvoiceBundle\Entity\File;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;
use Psys\OrderInvoiceBundle\Service\FileDeleter\FileDeleter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypes;


class FilePersister
{
    private const FILE_ENTITY_FQCN_DEFAULT = File::class;

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
    public function persistProforma(string $binary, Order $order): ?array
    {
        return $this->persist($binary, $order, InvoiceType::PROFORMA);
    }

    /**
      * Persists the advance invoice file binary to disk and saves a reference to it in the database using the default File entity.
      * If a custom File entity is used, only saves the file to disk and returns the file info for further processing.
      *
      * @return array|null file info array properties: mimeType, nameFileSystem, nameDisplay
     */
    public function persistAdvance(string $binary, InvoiceAdvance $invoiceAdvance): ?array
    {
        return $this->persist($binary, $invoiceAdvance->getOrder(), InvoiceType::ADVANCE, $invoiceAdvance);
    }

    /**
      * Persists the final invoice file binary to disk and saves a reference to it in the database using the default File entity.
      * If a custom File entity is used, only saves the file to disk and returns the file info for further processing.
      *
      * @return array|null file info array properties: mimeType, nameFileSystem, nameDisplay
     */
    public function persistFinal(string $binary, Order $order): ?array
    {
        return $this->persist($binary, $order, InvoiceType::FINAL);
    }

    /**
      * Persists the regular invoice file binary to disk and saves a reference to it in the database using the default File entity.
      * If a custom File entity is used, only saves the file to disk and returns the file info for further processing.
      *
      * @return array|null file info array properties: mimeType, nameFileSystem, nameDisplay
     */
    public function persistRegular(string $binary, Order $order): ?array
    {
        return $this->persist($binary, $order, InvoiceType::REGULAR);
    }

    /**
      * Persists the file binary to disk and saves a reference to it in the database using the default File entity.
      * If a custom File entity is used, only saves the file to disk and returns the file info for further processing.
      *
      * @return array|null file info array properties: mimeType, nameFileSystem, nameDisplay
     */
    private function persist(string $binary, Order $order, InvoiceType $invoiceType, ?InvoiceAdvance $invoiceAdvance = null): ?array
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
        else if ($invoiceType === InvoiceType::ADVANCE)
        {
            $storagePath = $this->storagePath['advance'];
            $nameDisplay = 'advance_invoice.'.$extension;
        }
        else if ($invoiceType === InvoiceType::FINAL)
        {
            $storagePath = $this->storagePath['final'];
            $nameDisplay = 'final_invoice.'.$extension;
        }
        else if ($invoiceType === InvoiceType::REGULAR)
        {
            $storagePath = $this->storagePath['regular'];
            $nameDisplay = 'regular_invoice.'.$extension;
        }

        // Default File entity is being used
        if ($this->fileEntityFQCN === self::FILE_ENTITY_FQCN_DEFAULT)
        {
            // Remove existing file to avoid orphaned files
            if ($invoiceType === InvoiceType::PROFORMA)
            {
                $this->fileDeleter->deleteProforma($order);
            }
            else if ($invoiceType === InvoiceType::ADVANCE)
            {
                $this->fileDeleter->deleteAdvance($invoiceAdvance);
            }
            else if ($invoiceType === InvoiceType::FINAL)
            {
                $this->fileDeleter->deleteFinal($order);
            }
            else if ($invoiceType === InvoiceType::REGULAR)
            {
                $this->fileDeleter->deleteRegular($order);
            }
        }

        // Save to disk
        $absPath = $this->filesystem->tempnam($this->projectDir.$storagePath, '', '.'.$extension); 
        $this->filesystem->dumpFile($absPath, $binary);
        $nameFileSystem = basename($absPath);
        
        // Save reference to the file in the database using the default File entity
        if ($this->fileEntityFQCN === self::FILE_ENTITY_FQCN_DEFAULT)
        {
            $file = (new $this->fileEntityFQCN())
                ->setMimeType($mimeType)
                ->setNameFileSystem($nameFileSystem)
                ->setNameDisplay($nameDisplay);

            if ($invoiceType === InvoiceType::PROFORMA)
            {
                $invoiceProforma = $order->getInvoiceProforma();
                $invoiceProforma->setFile($file);
                $this->em->persist($invoiceProforma);
            }
            else if ($invoiceType === InvoiceType::ADVANCE)
            {
                $invoiceAdvance->setFile($file);
                $this->em->persist($invoiceAdvance);
            }
            else if ($invoiceType === InvoiceType::FINAL)
            {
                $invoiceFinal = $order->getInvoiceFinal();
                $invoiceFinal->setFile($file);
                $this->em->persist($invoiceFinal);
            }
            else if ($invoiceType === InvoiceType::REGULAR)
            {
                $invoiceRegular = $order->getInvoiceRegular();
                $invoiceRegular->setFile($file);
                $this->em->persist($invoiceRegular);
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