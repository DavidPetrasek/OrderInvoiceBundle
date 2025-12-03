<?php

namespace Psys\OrderInvoiceBundle\Entity;

use Psys\OrderInvoiceBundle\Repository\FileRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Psys\OrderInvoiceBundle\Model\FileInterface;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\Table (name: 'oi_file')]
class File implements FileInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options:["unsigned" => true])]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private ?string $mime_type = null;

    #[ORM\Column(length: 50)]
    private ?string $name_file_system = null;

    #[ORM\Column(length: 200)]
    private ?string $name_display = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $created_at = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMimeType(): ?string
    {
        return $this->mime_type;
    }

    public function setMimeType(string $mime_type): self
    {
        $this->mime_type = $mime_type;

        return $this;
    }

    public function getNameFileSystem(): ?string
    {
        return $this->name_file_system;
    }

    public function setNameFileSystem(string $name_file_system): self
    {
        $this->name_file_system = $name_file_system;

        return $this;
    }

    public function getNameDisplay(): ?string
    {
        return $this->name_display;
    }

    public function setNameDisplay(string $name_display): self
    {
        $this->name_display = $name_display;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeImmutable $created_at = null): self
    {
        if ($created_at === null)
        {
            $created_at = new \DateTimeImmutable();
        }
        
        $this->created_at = $created_at;

        return $this;
    }
}
