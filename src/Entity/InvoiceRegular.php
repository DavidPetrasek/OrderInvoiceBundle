<?php

namespace Psys\OrderInvoiceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Psys\OrderInvoiceBundle\Repository\InvoiceRegularRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRegularRepository::class)]
#[ORM\Table (name: 'oi_invoice_regular')]
class InvoiceRegular
{
    use InvoiceTrait, MoneyTrait;
    
    #[ORM\OneToOne(mappedBy: 'invoice_regular', cascade: ['persist'])]
    private Order $order;

    #[ORM\OneToMany(mappedBy: 'invoice_regular', targetEntity: Item::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $items;


    public function __construct()
    {
        $this->items = new ArrayCollection();
    }


    public function getOrder(): ?Order
    {
        return $this->order;
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
            $item->setInvoiceRegular($this);
        }

        return $this;
    }

    public function removeItem(Item $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getInvoiceRegular() === $this) {
                $item->setInvoiceRegular(null);
            }
        }

        return $this;
    }
}
