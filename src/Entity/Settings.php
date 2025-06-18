<?php

namespace Psys\OrderInvoiceBundle\Entity;

use Psys\OrderInvoiceBundle\Repository\SettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
#[ORM\Table (name: 'oi_settings')]
class Settings
{    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::SMALLINT, options:["unsigned" => true])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $option = null;

    #[ORM\Column(length: 750, nullable: true)]
    private ?string $value = null;

    #[ORM\Column (options:["default" => "CURRENT_TIMESTAMP"]) ]
    private \DateTimeImmutable $changed_at;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOption(): ?string
    {
        return $this->option;
    }

    public function setOption(string $option): self
    {
        $this->option = $option;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getChangedAt(): \DateTimeImmutable
    {
        return $this->changed_at;
    }
    
    public function setChangedAt(\DateTimeImmutable $changed_at): self
    {        
        $this->changed_at = $changed_at;
        
        return $this;
    }
}
