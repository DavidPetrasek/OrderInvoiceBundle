<?php

namespace Psys\OrderInvoiceBundle\Entity;

use Psys\OrderInvoiceBundle\Repository\InvoiceRegularRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRegularRepository::class)]
#[ORM\Table (name: 'oi_invoice_regular')]
class InvoiceRegular
{
    use InvoiceTrait;
    
    #[ORM\OneToOne(mappedBy: 'invoice_regular', cascade: ['persist'])]
    private Invoice $invoice;
}
