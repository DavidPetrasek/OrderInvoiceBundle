<?= "<?php declare(strict_types=1);\n" ?>

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
        $this->addSql('ALTER TABLE oi_invoice_final ADD paid_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oi_invoice_final DROP paid_at');
    }
}
