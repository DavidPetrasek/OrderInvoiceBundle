<?php

namespace Psys\OrderInvoiceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Psys\OrderInvoiceBundle\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Psys\OrderInvoiceBundle\Model\Order\State;
use Psys\OrderInvoiceBundle\Model\CustomerInterface;
use Psys\OrderInvoiceBundle\Model\Order\CategoryInterface;


#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table (name: 'oi_order')]
class Order
{
    use MoneyTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options:["unsigned" => true])]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: Item::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $items;
    
    #[ORM\Column(insertable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeImmutable $created_at;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options:["unsigned" => true])]
    private ?int $category = null;

    #[ORM\ManyToOne(targetEntity: CustomerInterface::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?CustomerInterface $customer = null;

    #[ORM\Column(type: Types::SMALLINT, options:["unsigned" => true, 'default' => 1])]
    private ?int $state = 1;


    #[ORM\OneToOne(inversedBy: 'order', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?InvoiceRegular $invoice_regular = null;

    #[ORM\OneToOne(inversedBy: 'order', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?InvoiceProforma $invoice_proforma = null;
    
    /**
     * @var Collection<int, InvoiceAdvance>
     */
    #[ORM\OneToMany(targetEntity: InvoiceAdvance::class, mappedBy: 'order', cascade: ['persist', 'remove'])]
    private Collection $invoices_advance;

    #[ORM\OneToOne(inversedBy: 'order', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?InvoiceFinal $invoice_final = null;

    #[ORM\OneToOne(inversedBy: 'order', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Buyer $buyer = null;

    #[ORM\OneToOne(inversedBy: 'order', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Seller $seller = null;


    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->invoices_advance = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    /**
     * @return Collection<int, Item>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(Item $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    public function removeItem(Item $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getOrder() === $this) {
                $item->setOrder(null);
            }
        }

        return $this;
    }
    
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }
    
    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {        
        $this->created_at = $created_at;
        
        return $this;
    }

    public function getCategory(): ?CategoryInterface
    {
        return CategoryInterface::from($this->category);
    }

    public function setCategory(int|CategoryInterface|null $category): self
    {
        if ($category instanceof CategoryInterface) {$category = $category->value;}
        
        $this->category = $category;

        return $this;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getState(): State
    {
        return State::from($this->state);
    }

    public function setState(int|State $state): self
    {
        if ($state instanceof State) {$state = $state->value;}
        
        $this->state = $state;

        return $this;
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
            $invoice_advance->setOrder($this);
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

    public function getBuyer(): ?Buyer
    {
        return $this->buyer;
    }

    public function setBuyer(Buyer $buyer): self
    {
        $this->buyer = $buyer;

        return $this;
    }

    public function getSeller(): ?Seller
    {
        return $this->seller;
    }

    public function setSeller(Seller $seller): self
    {
        $this->seller = $seller;

        return $this;
    }
}
