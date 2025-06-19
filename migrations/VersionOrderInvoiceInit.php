<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class VersionOrderInvoiceInit extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO oi_settings (option, value) VALUES ('invoice_proforma_sequential_number', '1')");
        $this->addSql("INSERT INTO oi_settings (option, value) VALUES ('invoice_final_sequential_number','1')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM oi_settings WHERE option = 'invoice_proforma_sequential_number'");
        $this->addSql("DELETE FROM oi_settings WHERE option = 'invoice_final_sequential_number'");
    }
}
