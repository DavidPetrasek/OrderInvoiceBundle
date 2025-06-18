<?php

namespace Psys\OrderInvoiceBundle\Entity;

use Psys\OrderInvoiceBundle\Repository\InvoiceFinalRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceFinalRepository::class)]
#[ORM\Table (name: 'oi_invoice_final')]
class InvoiceFinal
{
    use InvoiceTrait;
    
    #[ORM\OneToOne(mappedBy: 'invoice_final', cascade: ['persist', 'remove'])]
    private ?Invoice $invoice = null;
}
