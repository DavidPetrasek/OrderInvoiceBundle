<?php

namespace Psys\OrderInvoiceBundle\Entity;

use Psys\OrderInvoiceBundle\Repository\InvoiceProformaRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: InvoiceProformaRepository::class)]
#[ORM\Table (name: 'oi_invoice_proforma')]
class InvoiceProforma
{
    use InvoiceTrait;
    
    #[ORM\OneToOne(mappedBy: 'invoice_proforma', cascade: ['persist'])]
    private Invoice $invoice;

    #[ORM\Column(options: ['default' => 0])]
    private ?bool $payable = false;


    public function isPayable(): ?bool
    {
        return $this->payable;
    }

    public function setPayable(bool $payable): static
    {
        $this->payable = $payable;

        return $this;
    }
}
