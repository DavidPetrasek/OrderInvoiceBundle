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
        $this->addSql('RENAME TABLE oi_invoice_buyer TO oi_buyer;');
        $this->addSql('RENAME TABLE oi_invoice_seller TO oi_seller;');

        $this->addSql('ALTER TABLE oi_invoice DROP FOREIGN KEY `FK_B2A9292419323A61`');
        $this->addSql('ALTER TABLE oi_invoice DROP FOREIGN KEY `FK_B2A92924A5259CBE`');
        $this->addSql('ALTER TABLE oi_invoice DROP FOREIGN KEY `FK_B2A92924CFC4175`');

        $this->addSql('ALTER TABLE oi_buyer DROP FOREIGN KEY `FK_A8FDC58B2989F1FD`');
        $this->addSql('DROP INDEX UNIQ_A8FDC58B2989F1FD ON oi_buyer');

        $this->addSql('ALTER TABLE oi_invoice_advance DROP FOREIGN KEY `FK_E0C4F13F2989F1FD`');
        $this->addSql('DROP INDEX IDX_E0C4F13F2989F1FD ON oi_invoice_advance');
        $this->addSql('ALTER TABLE oi_invoice_advance ADD payment_reference BIGINT UNSIGNED DEFAULT NULL, ADD order_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE oi_invoice_advance ADD CONSTRAINT FK_E0C4F13F8D9F6D38 FOREIGN KEY (order_id) REFERENCES oi_order (id)');
        $this->addSql('CREATE INDEX IDX_E0C4F13F8D9F6D38 ON oi_invoice_advance (order_id)');
        $this->addSql('ALTER TABLE oi_invoice_final ADD payment_reference BIGINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE oi_invoice_proforma ADD payment_reference BIGINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE oi_invoice_regular ADD payment_reference BIGINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE oi_order DROP FOREIGN KEY `FK_BC14BAEF2989F1FD`');
        $this->addSql('DROP INDEX UNIQ_BC14BAEF2989F1FD ON oi_order');
        $this->addSql('ALTER TABLE oi_order ADD invoice_proforma_id INT UNSIGNED DEFAULT NULL, ADD invoice_final_id INT UNSIGNED DEFAULT NULL, ADD buyer_id INT UNSIGNED DEFAULT NULL, ADD seller_id INT UNSIGNED DEFAULT NULL, ADD invoice_regular_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE oi_order ADD CONSTRAINT FK_BC14BAEF19323A61 FOREIGN KEY (invoice_regular_id) REFERENCES oi_invoice_regular (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE oi_order ADD CONSTRAINT FK_BC14BAEFA5259CBE FOREIGN KEY (invoice_proforma_id) REFERENCES oi_invoice_proforma (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE oi_order ADD CONSTRAINT FK_BC14BAEFCFC4175 FOREIGN KEY (invoice_final_id) REFERENCES oi_invoice_final (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE oi_order ADD CONSTRAINT FK_BC14BAEF6C755722 FOREIGN KEY (buyer_id) REFERENCES oi_buyer (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE oi_order ADD CONSTRAINT FK_BC14BAEF8DE820D9 FOREIGN KEY (seller_id) REFERENCES oi_seller (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC14BAEF19323A61 ON oi_order (invoice_regular_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC14BAEFA5259CBE ON oi_order (invoice_proforma_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC14BAEFCFC4175 ON oi_order (invoice_final_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC14BAEF6C755722 ON oi_order (buyer_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC14BAEF8DE820D9 ON oi_order (seller_id)');
        $this->addSql('ALTER TABLE oi_seller DROP FOREIGN KEY `FK_D33406F82989F1FD`');
        $this->addSql('DROP INDEX UNIQ_D33406F82989F1FD ON oi_seller');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oi_invoice ADD CONSTRAINT `FK_B2A9292419323A61` FOREIGN KEY (invoice_regular_id) REFERENCES oi_invoice_regular (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE oi_invoice ADD CONSTRAINT `FK_B2A92924A5259CBE` FOREIGN KEY (invoice_proforma_id) REFERENCES oi_invoice_proforma (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE oi_invoice ADD CONSTRAINT `FK_B2A92924CFC4175` FOREIGN KEY (invoice_final_id) REFERENCES oi_invoice_final (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE oi_buyer ADD CONSTRAINT `FK_A8FDC58B2989F1FD` FOREIGN KEY (invoice_id) REFERENCES oi_invoice (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A8FDC58B2989F1FD ON oi_buyer (invoice_id)');
        $this->addSql('ALTER TABLE oi_invoice_advance DROP FOREIGN KEY FK_E0C4F13F8D9F6D38');
        $this->addSql('DROP INDEX IDX_E0C4F13F8D9F6D38 ON oi_invoice_advance');
        $this->addSql('ALTER TABLE oi_invoice_advance DROP payment_reference, order_id');
        $this->addSql('ALTER TABLE oi_invoice_advance ADD CONSTRAINT `FK_E0C4F13F2989F1FD` FOREIGN KEY (invoice_id) REFERENCES oi_invoice (id)');
        $this->addSql('CREATE INDEX IDX_E0C4F13F2989F1FD ON oi_invoice_advance (invoice_id)');
        $this->addSql('ALTER TABLE oi_invoice_final DROP payment_reference');
        $this->addSql('ALTER TABLE oi_invoice_proforma DROP payment_reference');
        $this->addSql('ALTER TABLE oi_invoice_regular DROP payment_reference');
        $this->addSql('ALTER TABLE oi_order DROP FOREIGN KEY FK_BC14BAEF19323A61');
        $this->addSql('ALTER TABLE oi_order DROP FOREIGN KEY FK_BC14BAEFA5259CBE');
        $this->addSql('ALTER TABLE oi_order DROP FOREIGN KEY FK_BC14BAEFCFC4175');
        $this->addSql('ALTER TABLE oi_order DROP FOREIGN KEY FK_BC14BAEF6C755722');
        $this->addSql('ALTER TABLE oi_order DROP FOREIGN KEY FK_BC14BAEF8DE820D9');
        $this->addSql('DROP INDEX UNIQ_BC14BAEF19323A61 ON oi_order');
        $this->addSql('DROP INDEX UNIQ_BC14BAEFA5259CBE ON oi_order');
        $this->addSql('DROP INDEX UNIQ_BC14BAEFCFC4175 ON oi_order');
        $this->addSql('DROP INDEX UNIQ_BC14BAEF6C755722 ON oi_order');
        $this->addSql('DROP INDEX UNIQ_BC14BAEF8DE820D9 ON oi_order');
        $this->addSql('ALTER TABLE oi_order DROP invoice_regular_id, DROP invoice_proforma_id, DROP invoice_final_id, DROP buyer_id, DROP seller_id');
        $this->addSql('ALTER TABLE oi_order ADD CONSTRAINT `FK_BC14BAEF2989F1FD` FOREIGN KEY (invoice_id) REFERENCES oi_invoice (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC14BAEF2989F1FD ON oi_order (invoice_id)');
        $this->addSql('ALTER TABLE oi_seller ADD CONSTRAINT `FK_D33406F82989F1FD` FOREIGN KEY (invoice_id) REFERENCES oi_invoice (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D33406F82989F1FD ON oi_seller (invoice_id)');

        $this->addSql('RENAME TABLE oi_buyer TO oi_invoice_buyer;');
        $this->addSql('RENAME TABLE oi_seller TO oi_invoice_seller;'); 
    }
}
