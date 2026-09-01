<?php

declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Psys\OrderInvoiceBundle\Model\Order\PaymentMode;


trait MoneyTrait
{
    #[ORM\Column(type: Types::SMALLINT, nullable: true, options:["unsigned" => true])]
    private ?int $payment_mode = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $payment_mode_bank_account = null;
    
    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2, nullable: true)]
    private ?string $price_vat_included = '0.00';
    
    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2, nullable: true)]
    private ?string $price_vat_excluded = '0.00';
    
    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2, nullable: true)]
    private ?string $price_vat_base = '0.00';
    
    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 2, nullable: true)]
    private ?string $price_vat = '0.00';

    #[ORM\Column(length: 3, nullable: true, options:["fixed" => true, "comment" => "Three-letter alphabetic code (ISO 4217)"])]
    private ?string $currency = null;

    
    public function getPaymentMode(): ?PaymentMode
    {
        if (is_null($this->payment_mode)) {return null;}

        return PaymentMode::from($this->payment_mode);
    }
    
    public function setPaymentMode(null|int|PaymentMode $payment_mode): self
    {
        if ($payment_mode instanceof PaymentMode) {$payment_mode = $payment_mode->value;}
        
        $this->payment_mode = $payment_mode;
        
        return $this;
    }

    public function getPaymentModeBankAccount(): ?string
    {
        return $this->payment_mode_bank_account;
    }

    public function setPaymentModeBankAccount(?string $payment_mode_bank_account): self
    {
        $this->payment_mode_bank_account = $payment_mode_bank_account;

        return $this;
    }
    
    public function getPriceVatIncluded(): ?float
    {
        return $this->price_vat_included;
    }
    
    public function setPriceVatIncluded(?float $price_vat_included): self
    {
        $this->price_vat_included = $price_vat_included;
        
        return $this;
    }
    
    public function getPriceVatExcluded(): ?float
    {
        return $this->price_vat_excluded;
    }
    
    public function setPriceVatExcluded(?float $price_vat_excluded): self
    {
        $this->price_vat_excluded = $price_vat_excluded;
        
        return $this;
    }
    
    public function getPriceVatBase(): ?float
    {
        return $this->price_vat_base;
    }
    
    public function setPriceVatBase(?float $price_vat_base): self
    {
        $this->price_vat_base = $price_vat_base;
        
        return $this;
    }
    
    public function getPriceVat(): ?float
    {
        return $this->price_vat;
    }
    
    public function setPriceVat(?float $price_vat): self
    {
        $this->price_vat = $price_vat;
        
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }
    
    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;
        
        return $this;
    }
}
