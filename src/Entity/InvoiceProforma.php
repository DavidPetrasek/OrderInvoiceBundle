<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Psys\OrderInvoiceBundle\Repository\InvoiceProformaRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: InvoiceProformaRepository::class)]
#[ORM\Table (name: 'oi_invoice_proforma')]
class InvoiceProforma
{
    use InvoiceTrait;
    use MoneyTrait;
    use PaidTrait;
    
    #[ORM\OneToOne(mappedBy: 'invoice_proforma', cascade: ['persist'])]
    private Order $order;

    #[ORM\OneToMany(targetEntity: Item::class, mappedBy: 'invoice_proforma', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column(options: ['default' => 0])]
    private ?bool $payable = false;


    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function isPayable(): ?bool
    {
        return $this->payable;
    }

    public function setPayable(bool $payable): static
    {
        $this->payable = $payable;

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
            $item->setInvoiceProforma($this);
        }

        return $this;
    }

    public function removeItem(Item $item): static
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getInvoiceProforma() === $this) {
                $item->setInvoiceProforma(null);
            }
        }

        return $this;
    }
}
