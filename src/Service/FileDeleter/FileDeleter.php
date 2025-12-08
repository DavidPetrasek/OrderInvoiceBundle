<?php
namespace Psys\OrderInvoiceBundle\Service\FileDeleter;

use Doctrine\ORM\EntityManagerInterface;
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
     * Deletes the final invoice file from disk and removes its reference from the database.
     */
    public function deleteFinal(Order $ent_Order, ?string $nameFileSystem = null): void
    {
        $this->delete($ent_Order, InvoiceType::FINAL, $nameFileSystem);
    }

    /**
     * Deletes the file from disk and removes its reference from the database.
     */
    private function delete(Order $ent_Order, InvoiceType $invoiceType, ?string $nameFileSystem = null): void
    {        
        if ($invoiceType === InvoiceType::PROFORMA)
        {
            $storagePath = $this->storagePath['proforma'];
            $ent_InvoiceProforma = $ent_Order->getInvoice()->getInvoiceProforma();
            $ent_File = $ent_InvoiceProforma->getFile();
        }
        else if ($invoiceType === InvoiceType::FINAL)
        {
            $storagePath = $this->storagePath['final'];
            $ent_InvoiceFinal = $ent_Order->getInvoice()->getInvoiceFinal();
            $ent_File = $ent_InvoiceFinal->getFile();
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
        else if ($invoiceType === InvoiceType::FINAL)
        {
            $ent_InvoiceFinal->setFile(null);
            $this->em->persist($ent_InvoiceFinal);
        }
        
        $this->em->remove($ent_File);
        $this->em->flush();
    }
}