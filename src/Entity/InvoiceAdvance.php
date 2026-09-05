<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Psys\OrderInvoiceBundle\Repository\InvoiceAdvanceRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: InvoiceAdvanceRepository::class)]
#[ORM\Table (name: 'oi_invoice_advance')]
class InvoiceAdvance
{
    use InvoiceTrait;
    use MoneyTrait;
    use PaidTrait;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'invoices_advance')]
    private Order $order;

    #[ORM\OneToMany(targetEntity: Item::class, mappedBy: 'invoice_advance', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;


    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }
    public function setOrder(Order $order): self
    {
        $this->order = $order;

        return $this;
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
            $item->setInvoiceAdvance($this);
        }

        return $this;
    }

    public function removeItem(Item $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getInvoiceAdvance() === $this) {
                $item->setInvoiceAdvance(null);
            }
        }

        return $this;
    }
}
