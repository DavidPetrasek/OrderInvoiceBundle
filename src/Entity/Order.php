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
    
    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\OneToOne(inversedBy: 'order', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Invoice $invoice = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options:["unsigned" => true])]
    private ?int $category = null;

    #[ORM\ManyToOne(targetEntity: CustomerInterface::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?CustomerInterface $customer = null;

    #[ORM\Column(type: Types::SMALLINT, options:["unsigned" => true])]
    private ?int $state = null;


    public function __construct()
    {
        $this->items = new ArrayCollection();
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

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

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
}
