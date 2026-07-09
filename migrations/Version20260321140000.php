<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260321140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create portal table for CRM portal management';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE portal (id INT AUTO_INCREMENT NOT NULL, firma_id INT DEFAULT NULL, portal_key VARCHAR(80) NOT NULL, name VARCHAR(150) NOT NULL, portal_host VARCHAR(180) DEFAULT NULL, website_host VARCHAR(180) DEFAULT NULL, primary_color VARCHAR(7) NOT NULL, secondary_color VARCHAR(7) NOT NULL, logo_path VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, active TINYINT(1) NOT NULL, sort_order INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_3C1D4D3D20CD3F71 (portal_key), UNIQUE INDEX UNIQ_3C1D4D3D239BCBEF (portal_host), UNIQUE INDEX UNIQ_3C1D4D3DD4B08D00 (website_host), INDEX IDX_3C1D4D3D8F02BF9D (firma_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE portal ADD CONSTRAINT FK_3C1D4D3D8F02BF9D FOREIGN KEY (firma_id) REFERENCES firma (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal DROP FOREIGN KEY FK_3C1D4D3D8F02BF9D');
        $this->addSql('DROP TABLE portal');
    }
}
