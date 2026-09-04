<?php declare(strict_types=1);

namespace Psys\OrderInvoiceBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Psys\OrderInvoiceBundle\Entity\Seller;

class SellerTest extends TestCase
{
    public function testSellerInitialization(): void
    {
        $seller = new Seller();

        $this->assertNull($seller->getFullName());
        $this->assertNull($seller->getOrganization());
        $this->assertNull($seller->getStreetAddress1());
        $this->assertNull($seller->getStreetAddress2());
        $this->assertNull($seller->getCity());
        $this->assertNull($seller->getPostcode());
        $this->assertNull($seller->getCountry());
        $this->assertNull($seller->getVatIdentificationNumber());
        $this->assertNull($seller->getCompanyIdentificationNumber());
    }

    public function testSetAndGetSellerDetails(): void
    {
        $seller = new Seller();

        $seller
            ->setFullName('Company Owner')
            ->setOrganization('My Company Ltd.')
            ->setStreetAddress1('789 Business Street')
            ->setStreetAddress2('Suite 100')
            ->setCity('Ostrava')
            ->setPostcode('70200')
            ->setRegion('Moravian-Silesian')
            ->setCountry('Czech Republic')
            ->setVatIdentificationNumber('CZ99999999')
            ->setCompanyIdentificationNumber('99999999')
            ->setLegalEntityRegistrationDetails('Registered at Regional Court');

        $this->assertSame('Company Owner', $seller->getFullName());
        $this->assertSame('My Company Ltd.', $seller->getOrganization());
        $this->assertSame('789 Business Street', $seller->getStreetAddress1());
        $this->assertSame('Suite 100', $seller->getStreetAddress2());
        $this->assertSame('Ostrava', $seller->getCity());
        $this->assertSame('70200', $seller->getPostcode());
        $this->assertSame('Moravian-Silesian', $seller->getRegion());
        $this->assertSame('Czech Republic', $seller->getCountry());
        $this->assertSame('CZ99999999', $seller->getVatIdentificationNumber());
        $this->assertSame('99999999', $seller->getCompanyIdentificationNumber());
        $this->assertSame('Registered at Regional Court', $seller->getLegalEntityRegistrationDetails());
    }

    public function testOptionalAddressFields(): void
    {
        $seller = new Seller();

        $seller->setStreetAddress2(null);
        $seller->setRegion(null);

        $this->assertNull($seller->getStreetAddress2());
        $this->assertNull($seller->getRegion());
    }

    public function testChainedSetters(): void
    {
        $seller = new Seller();

        $result = $seller
            ->setFullName('Test Name')
            ->setOrganization('Test Org')
            ->setCity('Test City');

        $this->assertSame($seller, $result);
    }
}
