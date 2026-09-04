<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\Buyer;
use Psys\OrderInvoiceBundle\Entity\Seller;
use Psys\OrderInvoiceBundle\Entity\Order;

class BuyerTest extends TestCase
{
    public function testBuyerInitialization(): void
    {
        $buyer = new Buyer();

        $this->assertNull($buyer->getFullName());
        $this->assertNull($buyer->getOrganization());
        $this->assertNull($buyer->getStreetAddress1());
        $this->assertNull($buyer->getStreetAddress2());
        $this->assertNull($buyer->getCity());
        $this->assertNull($buyer->getPostcode());
        $this->assertNull($buyer->getCountry());
        $this->assertNull($buyer->getVatIdentificationNumber());
        $this->assertNull($buyer->getCompanyIdentificationNumber());
    }

    public function testSetAndGetFullName(): void
    {
        $buyer = new Buyer();
        $name = 'John Doe';

        $result = $buyer->setFullName($name);

        $this->assertSame($buyer, $result);
        $this->assertSame($name, $buyer->getFullName());
    }

    public function testSetAndGetOrganization(): void
    {
        $buyer = new Buyer();
        $org = 'Acme Corporation';

        $result = $buyer->setOrganization($org);

        $this->assertSame($buyer, $result);
        $this->assertSame($org, $buyer->getOrganization());
    }

    public function testSetAndGetStreetAddresses(): void
    {
        $buyer = new Buyer();
        $addr1 = '123 Main Street';
        $addr2 = 'Apt 4B';

        $buyer->setStreetAddress1($addr1);
        $result = $buyer->setStreetAddress2($addr2);

        $this->assertSame($buyer, $result);
        $this->assertSame($addr1, $buyer->getStreetAddress1());
        $this->assertSame($addr2, $buyer->getStreetAddress2());
    }

    public function testSetAndGetCityPostcodeRegion(): void
    {
        $buyer = new Buyer();

        $buyer->setCity('Prague');
        $buyer->setPostcode('11000');

        $result = $buyer->setRegion('Prague Region');

        $this->assertSame($buyer, $result);
        $this->assertSame('Prague', $buyer->getCity());
        $this->assertSame('11000', $buyer->getPostcode());
        $this->assertSame('Prague Region', $buyer->getRegion());
    }

    public function testSetAndGetCountry(): void
    {
        $buyer = new Buyer();
        $country = 'Czech Republic';

        $result = $buyer->setCountry($country);

        $this->assertSame($buyer, $result);
        $this->assertSame($country, $buyer->getCountry());
    }

    public function testSetAndGetVatIdentificationNumber(): void
    {
        $buyer = new Buyer();
        $vat = 'CZ12345678';

        $result = $buyer->setVatIdentificationNumber($vat);

        $this->assertSame($buyer, $result);
        $this->assertSame($vat, $buyer->getVatIdentificationNumber());
    }

    public function testSetAndGetCompanyIdentificationNumber(): void
    {
        $buyer = new Buyer();
        $cin = '12345678';

        $result = $buyer->setCompanyIdentificationNumber($cin);

        $this->assertSame($buyer, $result);
        $this->assertSame($cin, $buyer->getCompanyIdentificationNumber());
    }

    public function testSetLegalEntityRegistrationDetails(): void
    {
        $buyer = new Buyer();
        $details = 'Registered at District Court';

        $result = $buyer->setLegalEntityRegistrationDetails($details);

        $this->assertSame($buyer, $result);
        $this->assertSame($details, $buyer->getLegalEntityRegistrationDetails());
    }

    public function testCompleteAddressData(): void
    {
        $buyer = new Buyer();

        $buyer
            ->setFullName('Jane Smith')
            ->setOrganization('Tech Inc.')
            ->setStreetAddress1('456 Tech Avenue')
            ->setCity('Brno')
            ->setPostcode('60200')
            ->setCountry('Czech Republic')
            ->setVatIdentificationNumber('CZ87654321')
            ->setCompanyIdentificationNumber('87654321');

        $this->assertSame('Jane Smith', $buyer->getFullName());
        $this->assertSame('Tech Inc.', $buyer->getOrganization());
        $this->assertSame('456 Tech Avenue', $buyer->getStreetAddress1());
        $this->assertSame('Brno', $buyer->getCity());
        $this->assertSame('60200', $buyer->getPostcode());
        $this->assertSame('Czech Republic', $buyer->getCountry());
        $this->assertSame('CZ87654321', $buyer->getVatIdentificationNumber());
        $this->assertSame('87654321', $buyer->getCompanyIdentificationNumber());
    }
}
