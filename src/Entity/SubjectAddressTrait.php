<?php
namespace Psys\OrderInvoiceBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


trait SubjectAddressTrait
{
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $full_name = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $organization = null;
    
    #[ORM\Column(length: 128, nullable: true)]
    private ?string $street_address_1 = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $street_address_2 = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 12, nullable: true)]
    private ?string $postcode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $vat_identification_number  = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $company_identification_number = null;

    /**
     * Registered office address, jurisdiction, ...
     */
    #[ORM\Column(length: 200, nullable: true)]
    private ?string $legal_entity_registration_details = null;
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }
    
    public function getFullName(): ?string
    {
        return $this->full_name;
    }
    
    public function setFullName(string $full_name): self
    {
        $this->full_name = $full_name;
        
        return $this;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }
    
    public function setOrganization(string $organization): self
    {
        $this->organization = $organization;
        
        return $this;
    }

    public function getStreetAddress1(): ?string
    {
        return $this->street_address_1;
    }

    public function setStreetAddress1(?string $street_address_1): self
    {
        $this->street_address_1 = $street_address_1;

        return $this;
    }

    public function getStreetAddress2(): ?string
    {
        return $this->street_address_2;
    }

    public function setStreetAddress2(?string $street_address_2): self
    {
        $this->street_address_2 = $street_address_2;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(?string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }
    
    public function getVatIdentificationNumber(): ?string
    {
        return $this->vat_identification_number;
    }

    public function setVatIdentificationNumber(?string $vat_identification_number): self
    {
        $this->vat_identification_number = $vat_identification_number;

        return $this;
    }

    public function getCompanyIdentificationNumber(): ?string
    {
        return $this->company_identification_number;
    }

    public function setCompanyIdentificationNumber(?string $company_identification_number): self
    {
        $this->company_identification_number = $company_identification_number;

        return $this;
    }

    public function getLegalEntityRegistrationDetails(): ?string
    {
        return $this->legal_entity_registration_details;
    }
    
    public function setLegalEntityRegistrationDetails(string $legal_entity_registration_details): self
    {
        $this->legal_entity_registration_details = $legal_entity_registration_details;
        
        return $this;
    }
}





?>