<?php

namespace Psys\OrderInvoiceBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Model\OrderItem\AmountType;
use Psys\OrderInvoiceBundle\Model\OrderItem\CategoryInterface;
use Psys\OrderInvoiceBundle\Repository\OrderItemRepository;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table (name: 'oi_order_item')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options:["unsigned" => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\Column(length: 80, nullable: true)]
    private string $name;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $short_description = null;
    
    #[ORM\Column(type: Types::SMALLINT, nullable: true, options:["unsigned" => true])]
    private ?int $category = null;

    #[ORM\Column(type: Types::SMALLINT, options:["unsigned" => true])]
    private int $amount;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2)]
    private string $price_vat_included = '0.00';
    
    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2)]
    private string $price_vat_excluded = '0.00';
    
    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2)]
    private string $vat_rate = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2)]
    private string $vat = '0.00';

    #[ORM\Column(type: Types::SMALLINT, options:["unsigned" => true])]
    private int $amount_type;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->short_description;
    }

    public function setShortDescription(string $short_description): static
    {
        $this->short_description = $short_description;

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

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPriceVatExcluded(): float
    {
        return $this->price_vat_excluded;
    }
    
    public function setPriceVatExcluded(float $price_vat_excluded): self
    {
        $this->price_vat_excluded = $price_vat_excluded;
        
        return $this;
    }

    public function getPriceVatIncluded(): float
    {
        return $this->price_vat_included;
    }
    
    public function setPriceVatIncluded(float $price_vat_included): self
    {
        $this->price_vat_included = $price_vat_included;
        
        return $this;
    }

    public function getVatRate(): float
    {
        return $this->vat_rate;
    }
    
    public function setVatRate(float $vat_rate): self
    {
        $this->vat_rate = $vat_rate;
        
        return $this;
    }

    public function getVat(): float
    {
        return $this->vat;
    }
    
    public function setVat(float $vat): self
    {
        $this->vat = $vat;
        
        return $this;
    }

    public function getAmountType(): AmountType
    {
        return AmountType::from($this->amount_type);
    }

    public function setAmountType(int|AmountType $amount_type): self
    {
        if ($amount_type instanceof AmountType) {$amount_type = $amount_type->value;}
        
        $this->amount_type = $amount_type;

        return $this;
    }
}
