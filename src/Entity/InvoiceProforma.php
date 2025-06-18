<?php

namespace Psys\OrderInvoiceBundle\Entity;

use Psys\OrderInvoiceBundle\Repository\InvoiceProformaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceProformaRepository::class)]
#[ORM\Table (name: 'oi_invoice_proforma')]
class InvoiceProforma
{
    use InvoiceTrait;
    
    #[ORM\OneToOne(mappedBy: 'invoice_proforma', cascade: ['persist', 'remove'])]
    private ?Invoice $invoice = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private \DateTimeImmutable $due_date;

    public function getDueDate(): \DateTimeImmutable
    {
        return $this->due_date;
    }

    public function setDueDate(\DateTimeImmutable $due_date): self
    {
        $this->due_date = $due_date;

        return $this;
    }
}
