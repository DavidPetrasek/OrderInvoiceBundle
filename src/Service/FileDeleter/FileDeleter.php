<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Service\FileDeleter;

use Doctrine\ORM\EntityManagerInterface;
use Psys\OrderInvoiceBundle\Entity\InvoiceAdvance;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\FileInterface;
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
    public function deleteProforma(Order $order, ?string $nameFileSystem = null): void
    {
        $this->delete($order, InvoiceType::PROFORMA, $nameFileSystem);
    }

    /**
     * Deletes the advance invoice file from disk and removes its reference from the database.
     */
    public function deleteAdvance(InvoiceAdvance $invoiceAdvance, ?string $nameFileSystem = null): void
    {
        $this->delete($invoiceAdvance->getOrder(), InvoiceType::ADVANCE, $nameFileSystem, $invoiceAdvance);
    }

    /**
     * Deletes the final invoice file from disk and removes its reference from the database.
     */
    public function deleteFinal(Order $order, ?string $nameFileSystem = null): void
    {
        $this->delete($order, InvoiceType::FINAL, $nameFileSystem);
    }

    /**
     * Deletes the regular invoice file from disk and removes its reference from the database.
     */
    public function deleteRegular(Order $order, ?string $nameFileSystem = null): void
    {
        $this->delete($order, InvoiceType::REGULAR, $nameFileSystem);
    }

    /**
     * Deletes the file from disk and removes its reference from the database.
     */
    private function delete(Order $order, InvoiceType $invoiceType, ?string $nameFileSystem = null, ?InvoiceAdvance $invoiceAdvance = null): void
    {        
        if ($invoiceType === InvoiceType::PROFORMA)
        {
            $storagePath = $this->storagePath['proforma'];
            $invoiceProforma = $order->getInvoiceProforma();
            $file = $invoiceProforma->getFile();
        }
        else if ($invoiceType === InvoiceType::ADVANCE)
        {
            $storagePath = $this->storagePath['advance'];
            $file = $invoiceAdvance->getFile();
        }
        else if ($invoiceType === InvoiceType::FINAL)
        {
            $storagePath = $this->storagePath['final'];
            $invoiceFinal = $order->getInvoiceFinal();
            $file = $invoiceFinal->getFile();
        }
        else if ($invoiceType === InvoiceType::REGULAR)
        {
            $storagePath = $this->storagePath['regular'];
            $invoiceRegular = $order->getInvoiceRegular();
            $file = $invoiceRegular->getFile();
        }

        // No file to delete
        if (!$file instanceof FileInterface) {return;}

        $nameFileSystem ??= $file->getNameFileSystem();

        // Delete from disk
        $this->filesystem->remove($this->projectDir.$storagePath.'/'.$nameFileSystem);

        // Remove reference to the file from the database
        if ($invoiceType === InvoiceType::PROFORMA)
        {
            $invoiceProforma->setFile(null);
            $this->em->persist($invoiceProforma);
        }
        else if ($invoiceType === InvoiceType::ADVANCE)
        {
            $invoiceAdvance->setFile(null);
            $this->em->persist($invoiceAdvance);
        }
        else if ($invoiceType === InvoiceType::FINAL)
        {
            $invoiceFinal->setFile(null);
            $this->em->persist($invoiceFinal);
        }
        else if ($invoiceType === InvoiceType::REGULAR)
        {
            $invoiceRegular->setFile(null);
            $this->em->persist($invoiceRegular);
        }

        $this->em->remove($file);
        $this->em->flush();
    }
}
