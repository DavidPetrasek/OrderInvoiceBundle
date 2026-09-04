<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Entity;

use Psys\OrderInvoiceBundle\Repository\SellerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SellerRepository::class)]
#[ORM\Table (name: 'oi_seller')]
class Seller
{
    use SubjectAddressTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options:["unsigned" => true])]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'seller', cascade: ['persist'])]
    private Order $order;
}
