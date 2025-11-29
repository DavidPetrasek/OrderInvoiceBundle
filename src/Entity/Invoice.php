<?php

namespace Psys\OrderInvoiceBundle\Entity;

use Psys\OrderInvoiceBundle\Repository\InvoiceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[ORM\Table (name: 'oi_invoice')]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options:["unsigned" => true])]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'], inversedBy: 'invoice')]
    #[ORM\JoinColumn(nullable: true)]
    private ?InvoiceProforma $invoice_proforma = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'], inversedBy: 'invoice')]
    private ?InvoiceFinal $invoice_final = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options:["unsigned" => true])]
    private ?string $variable_symbol = null;

    #[ORM\OneToOne(mappedBy: 'invoice', cascade: ['persist', 'remove'])]
    private ?Order $order = null;

    #[ORM\OneToOne(mappedBy: 'invoice', cascade: ['persist', 'remove'])]
    private ?InvoiceBuyer $invoice_buyer = null;

    #[ORM\OneToOne(mappedBy: 'invoice', cascade: ['persist', 'remove'])]
    private ?InvoiceSeller $invoice_seller = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceProforma(): ?InvoiceProforma
    {
        return $this->invoice_proforma;
    }

    public function setInvoiceProforma(InvoiceProforma $invoice_proforma): self
    {
        $this->invoice_proforma = $invoice_proforma;

        return $this;
    }

    public function getInvoiceFinal(): ?InvoiceFinal
    {
        return $this->invoice_final;
    }

    public function setInvoiceFinal(?InvoiceFinal $invoice_final): self
    {
        $this->invoice_final = $invoice_final;

        return $this;
    }

  

    public function getVariableSymbol(): ?string
    {
        return $this->variable_symbol;
    }

    public function setVariableSymbol(?string $variable_symbol): self
    {
        $this->variable_symbol = $variable_symbol;

        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): self
    {
        // set the owning side of the relation if necessary
        if ($order->getInvoice() !== $this) {
            $order->setInvoice($this);
        }

        $this->order = $order;

        return $this;
    }

    public function getInvoiceBuyer(): ?InvoiceBuyer
    {
        return $this->invoice_buyer;
    }

    public function setInvoiceBuyer(InvoiceBuyer $invoice_buyer): self
    {
        // set the owning side of the relation if necessary
        if ($invoice_buyer->getInvoice() !== $this) {
            $invoice_buyer->setInvoice($this);
        }

        $this->invoice_buyer = $invoice_buyer;

        return $this;
    }

    public function getInvoiceSeller(): ?InvoiceSeller
    {
        return $this->invoice_seller;
    }

    public function setInvoiceSeller(InvoiceSeller $invoice_seller): self
    {
        // set the owning side of the relation if necessary
        if ($invoice_seller->getInvoice() !== $this) {
            $invoice_seller->setInvoice($this);
        }

        $this->invoice_seller = $invoice_seller;

        return $this;
    }
}
