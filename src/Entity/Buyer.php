<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Entity;

use Psys\OrderInvoiceBundle\Repository\BuyerRepository;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: BuyerRepository::class)]
#[ORM\Table (name: 'oi_buyer')]
class Buyer
{
    use SubjectAddressTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options:["unsigned" => true])]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'buyer', cascade: ['persist'])]
    private Order $order;
}
