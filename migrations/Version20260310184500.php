<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310184500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create lexware_invoice table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE lexware_invoice (id INT AUTO_INCREMENT NOT NULL, kunde_id INT DEFAULT NULL, lexware_invoice_id VARCHAR(64) NOT NULL, voucher_number VARCHAR(64) DEFAULT NULL, status VARCHAR(20) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_70BEA5B4B2C71D3B (lexware_invoice_id), INDEX IDX_70BEA5B44BAD5A4B (kunde_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lexware_invoice ADD CONSTRAINT FK_70BEA5B44BAD5A4B FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE lexware_invoice DROP FOREIGN KEY FK_70BEA5B44BAD5A4B');
        $this->addSql('DROP TABLE lexware_invoice');
    }
}
