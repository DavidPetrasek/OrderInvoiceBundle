<?= "<?php\n" ?>

declare(strict_types=1);

namespace DoctrineMigrations;

<?= $use_statements ?>

final class <?= $class_name ?> extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE oi_invoice_advance (due_date DATE NOT NULL, id INT UNSIGNED AUTO_INCREMENT NOT NULL, sequential_number BIGINT NOT NULL, reference_number BIGINT NOT NULL, created_at DATETIME NOT NULL, paid_at DATETIME DEFAULT NULL, payment_mode SMALLINT UNSIGNED NOT NULL, payment_mode_bank_account VARCHAR(20) DEFAULT NULL, price_vat_included NUMERIC(14, 2) DEFAULT NULL, price_vat_excluded NUMERIC(14, 2) DEFAULT NULL, price_vat_base NUMERIC(14, 2) DEFAULT NULL, price_vat NUMERIC(14, 2) DEFAULT NULL, currency CHAR(3) NOT NULL COMMENT \'Three-letter alphabetic code (ISO 4217)\', invoice_id INT UNSIGNED DEFAULT NULL, file_id INT UNSIGNED DEFAULT NULL, INDEX IDX_E0C4F13F2989F1FD (invoice_id), UNIQUE INDEX UNIQ_E0C4F13F93CB796C (file_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE oi_invoice_regular (due_date DATE NOT NULL, id INT UNSIGNED AUTO_INCREMENT NOT NULL, sequential_number BIGINT NOT NULL, reference_number BIGINT NOT NULL, created_at DATETIME NOT NULL, file_id INT UNSIGNED DEFAULT NULL, UNIQUE INDEX UNIQ_4D34E91893CB796C (file_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');  
        $this->addSql('ALTER TABLE oi_invoice_advance ADD CONSTRAINT FK_E0C4F13F2989F1FD FOREIGN KEY (invoice_id) REFERENCES oi_invoice (id)');
        $this->addSql('ALTER TABLE oi_invoice_advance ADD CONSTRAINT FK_E0C4F13F93CB796C FOREIGN KEY (file_id) REFERENCES oi_file (id)');
        $this->addSql('ALTER TABLE oi_invoice_regular ADD CONSTRAINT FK_4D34E91893CB796C FOREIGN KEY (file_id) REFERENCES oi_file (id)');
        $this->addSql('ALTER TABLE oi_invoice ADD invoice_regular_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE oi_invoice ADD CONSTRAINT FK_B2A9292419323A61 FOREIGN KEY (invoice_regular_id) REFERENCES oi_invoice_regular (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B2A9292419323A61 ON oi_invoice (invoice_regular_id)');

        $this->addSql('RENAME TABLE oi_order_item TO oi_item;');

        $this->addSql('ALTER TABLE oi_item ADD invoice_advance_id INT UNSIGNED DEFAULT NULL, CHANGE order_id order_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE oi_item ADD CONSTRAINT FK_4B751FF81B9600EC FOREIGN KEY (invoice_advance_id) REFERENCES oi_invoice_advance (id)');
        $this->addSql('CREATE INDEX IDX_4B751FF81B9600EC ON oi_item (invoice_advance_id)');
        $this->addSql('ALTER TABLE oi_item RENAME INDEX idx_c92a41ed8d9f6d38 TO IDX_4B751FF88D9F6D38');

        $this->addSql("INSERT INTO oi_settings (option, value) VALUES ('invoice_regular_sequential_number','1')");
        $this->addSql("INSERT INTO oi_settings (option, value) VALUES ('invoice_advance_sequential_number','1')");

        $this->addSql('ALTER TABLE oi_invoice CHANGE variable_symbol payment_reference BIGINT UNSIGNED DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oi_invoice CHANGE payment_reference variable_symbol BIGINT UNSIGNED DEFAULT NULL');

        $this->addSql("DELETE FROM `oi_settings` WHERE `option` = 'invoice_regular_sequential_number'");
        $this->addSql("DELETE FROM `oi_settings` WHERE `option` = 'invoice_advance_sequential_number'");
        
        $this->addSql('ALTER TABLE oi_item DROP FOREIGN KEY FK_4B751FF81B9600EC');
        $this->addSql('DROP INDEX IDX_4B751FF81B9600EC ON oi_item');
        $this->addSql('ALTER TABLE oi_item DROP invoice_advance_id, CHANGE order_id order_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE oi_item RENAME INDEX idx_4b751ff88d9f6d38 TO IDX_C92A41ED8D9F6D38');

        $this->addSql('RENAME TABLE oi_item TO oi_order_item;');

        $this->addSql('ALTER TABLE oi_invoice_advance DROP FOREIGN KEY FK_E0C4F13F2989F1FD');
        $this->addSql('ALTER TABLE oi_invoice_advance DROP FOREIGN KEY FK_E0C4F13F93CB796C');
        $this->addSql('ALTER TABLE oi_invoice_regular DROP FOREIGN KEY FK_4D34E91893CB796C');
        $this->addSql('DROP TABLE oi_invoice_advance');
        $this->addSql('DROP TABLE oi_invoice_regular');
        $this->addSql('ALTER TABLE oi_invoice DROP FOREIGN KEY FK_B2A9292419323A61');
        $this->addSql('DROP INDEX UNIQ_B2A9292419323A61 ON oi_invoice');
        $this->addSql('ALTER TABLE oi_invoice DROP invoice_regular_id');
    }
}
