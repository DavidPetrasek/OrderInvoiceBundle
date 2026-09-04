<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


trait PaidTrait
{
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paid_at = null;


    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paid_at;
    }

    public function setPaidAt(?\DateTimeImmutable $paid_at): self
    {        
        $this->paid_at = $paid_at;

        return $this;
    }

    public function setPaid(bool $paid): self
    {
        $this->paid_at = $paid ? new \DateTimeImmutable() : null;

        return $this;
    }

    public function isPaid(): bool
    {
        return !empty($this->paid_at);
    }
}
