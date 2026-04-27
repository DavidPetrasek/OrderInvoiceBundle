<?php
namespace Psys\OrderInvoiceBundle\Service\FileDeleter;

use Doctrine\ORM\EntityManagerInterface;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\Invoice\InvoiceType;
use Symfony\Component\Filesystem\Filesystem;


class FileDeleter
{
    public function __construct
    (
        private readonly Filesystem $filesystem,
        private readonly EntityManagerInterface $em,
        private readonly string $projectDir,
        private readonly array $storagePath // Yaml config
    )
    {}

    /**
     * Deletes the proforma invoice from disk and removes its reference from the database.
     */
    public function deleteProforma(Order $ent_Order, ?string $nameFileSystem = null): void
    {
        $this->delete($ent_Order, InvoiceType::PROFORMA, $nameFileSystem);
    }

    /**
     * Deletes the advance invoice file from disk and removes its reference from the database.
     */
    public function deleteAdvance(InvoiceAdvance $ent_InvoiceAdvance, ?string $nameFileSystem = null): void
    {
        $this->delete($ent_InvoiceAdvance->getOrder(), InvoiceType::ADVANCE, $nameFileSystem, $ent_InvoiceAdvance);
    }

    /**
     * Deletes the final invoice file from disk and removes its reference from the database.
     */
    public function deleteFinal(Order $ent_Order, ?string $nameFileSystem = null): void
    {
        $this->delete($ent_Order, InvoiceType::FINAL, $nameFileSystem);
    }

    /**
     * Deletes the regular invoice file from disk and removes its reference from the database.
     */
    public function deleteRegular(Order $ent_Order, ?string $nameFileSystem = null): void
    {
        $this->delete($ent_Order, InvoiceType::REGULAR, $nameFileSystem);
    }

    /**
     * Deletes the file from disk and removes its reference from the database.
     */
    private function delete(Order $ent_Order, InvoiceType $invoiceType, ?string $nameFileSystem = null, ?InvoiceAdvance $ent_InvoiceAdvance = null): void
    {        
        if ($invoiceType === InvoiceType::PROFORMA)
        {
            $storagePath = $this->storagePath['proforma'];
            $ent_InvoiceProforma = $ent_Order->getInvoiceProforma();
            $ent_File = $ent_InvoiceProforma->getFile();
        }
        else if ($invoiceType === InvoiceType::ADVANCE)
        {
            $storagePath = $this->storagePath['advance'];
            $ent_File = $ent_InvoiceAdvance->getFile();
        }
        else if ($invoiceType === InvoiceType::FINAL)
        {
            $storagePath = $this->storagePath['final'];
            $ent_InvoiceFinal = $ent_Order->getInvoiceFinal();
            $ent_File = $ent_InvoiceFinal->getFile();
        }
        else if ($invoiceType === InvoiceType::REGULAR)
        {
            $storagePath = $this->storagePath['regular'];
            $ent_InvoiceRegular = $ent_Order->getInvoiceRegular();
            $ent_File = $ent_InvoiceRegular->getFile();
        }

        // No file to delete
        if ($ent_File === null) {return;}

        if ($nameFileSystem === null) {$nameFileSystem = $ent_File->getNameFileSystem();}

        // Delete from disk
        $this->filesystem->remove($this->projectDir.$storagePath.'/'.$nameFileSystem);
        
        // Remove reference to the file from the database
        if ($invoiceType === InvoiceType::PROFORMA)
        {
            $ent_InvoiceProforma->setFile(null);
            $this->em->persist($ent_InvoiceProforma);
        }
        else if ($invoiceType === InvoiceType::ADVANCE)
        {
            $ent_InvoiceAdvance->setFile(null);
            $this->em->persist($ent_InvoiceAdvance);
        }
        else if ($invoiceType === InvoiceType::FINAL)
        {
            $ent_InvoiceFinal->setFile(null);
            $this->em->persist($ent_InvoiceFinal);
        }
        else if ($invoiceType === InvoiceType::REGULAR)
        {
            $ent_InvoiceRegular->setFile(null);
            $this->em->persist($ent_InvoiceRegular);
        }
        
        $this->em->remove($ent_File);
        $this->em->flush();
    }
}