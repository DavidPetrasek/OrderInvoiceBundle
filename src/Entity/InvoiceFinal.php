<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Entity;

use Psys\OrderInvoiceBundle\Repository\InvoiceFinalRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: InvoiceFinalRepository::class)]
#[ORM\Table (name: 'oi_invoice_final')]
class InvoiceFinal
{
    use InvoiceTrait;
    use PaidTrait;
    
    #[ORM\OneToOne(mappedBy: 'invoice_final', cascade: ['persist'])]
    private Order $order;


    public function getOrder(): ?Order
    {
        return $this->order;
    }
}
