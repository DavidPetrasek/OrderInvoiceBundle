<?php

namespace Psys\OrderInvoiceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?InvoiceRegular $invoice_regular = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'], inversedBy: 'invoice')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?InvoiceProforma $invoice_proforma = null;
    
    /**
     * @var Collection<int, InvoiceAdvance>
     */
    #[ORM\OneToMany(targetEntity: InvoiceAdvance::class, mappedBy: 'invoice', cascade: ['persist', 'remove'])]
    private Collection $invoices_advance;

    #[ORM\OneToOne(cascade: ['persist', 'remove'], inversedBy: 'invoice')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?InvoiceFinal $invoice_final = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true, options:["unsigned" => true])]
    private ?string $payment_reference = null;

    #[ORM\OneToOne(mappedBy: 'invoice', cascade: ['persist'])]
    private ?Order $order = null;

    #[ORM\OneToOne(mappedBy: 'invoice', cascade: ['persist', 'remove'])]
    private ?InvoiceBuyer $invoice_buyer = null;

    #[ORM\OneToOne(mappedBy: 'invoice', cascade: ['persist', 'remove'])]
    private ?InvoiceSeller $invoice_seller = null;


    public function __construct()
    {
        $this->invoices_advance = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoiceRegular(): ?InvoiceRegular
    {
        return $this->invoice_regular;
    }

    public function setInvoiceRegular(?InvoiceRegular $invoice_regular): self
    {
        $this->invoice_regular = $invoice_regular;

        return $this;
    }

    public function getInvoiceProforma(): ?InvoiceProforma
    {
        return $this->invoice_proforma;
    }

    public function setInvoiceProforma(?InvoiceProforma $invoice_proforma): self
    {
        $this->invoice_proforma = $invoice_proforma;

        return $this;
    }

    /**
     * @return Collection<int, InvoiceAdvance>
     */
    public function getInvoicesAdvance(): Collection
    {
        return $this->invoices_advance;
    }

    public function addInvoiceAdvance(InvoiceAdvance $invoice_advance): static
    {
        if (!$this->invoices_advance->contains($invoice_advance)) {
            $this->invoices_advance->add($invoice_advance);
            $invoice_advance->setInvoice($this);
        }

        return $this;
    }

    public function removeInvoiceAdvance(InvoiceAdvance $invoice_advance): static
    {
        $this->invoices_advance->removeElement($invoice_advance);

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

  

    public function getPaymentReference(): ?string
    {
        return $this->payment_reference;
    }

    public function setPaymentReference(?string $payment_reference): self
    {
        $this->payment_reference = $payment_reference;

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
