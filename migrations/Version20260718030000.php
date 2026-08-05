<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260718030000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Konzern master data entity (name, Sitz der Gesellschaft, Logo, Marken-Zuordnung)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE konzern (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, strasse VARCHAR(255) DEFAULT NULL, hausnummer VARCHAR(255) DEFAULT NULL, plz VARCHAR(20) DEFAULT NULL, stadt VARCHAR(120) DEFAULT NULL, land VARCHAR(120) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, active TINYINT(1) NOT NULL, logo_media_asset_id INT DEFAULT NULL, INDEX IDX_36E0ADDAA534876F (logo_media_asset_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE konzern_brand_master (konzern_id INT NOT NULL, brand_master_id INT NOT NULL, INDEX IDX_C3E42D1F5DFC553C (konzern_id), INDEX IDX_C3E42D1F79C4CB70 (brand_master_id), PRIMARY KEY(konzern_id, brand_master_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE konzern ADD CONSTRAINT FK_36E0ADDAA534876F FOREIGN KEY (logo_media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE konzern_brand_master ADD CONSTRAINT FK_C3E42D1F5DFC553C FOREIGN KEY (konzern_id) REFERENCES konzern (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE konzern_brand_master ADD CONSTRAINT FK_C3E42D1F79C4CB70 FOREIGN KEY (brand_master_id) REFERENCES tx_chiptuningdatabase_brand_master (uid) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE konzern DROP FOREIGN KEY FK_36E0ADDAA534876F');
        $this->addSql('ALTER TABLE konzern_brand_master DROP FOREIGN KEY FK_C3E42D1F5DFC553C');
        $this->addSql('ALTER TABLE konzern_brand_master DROP FOREIGN KEY FK_C3E42D1F79C4CB70');
        $this->addSql('DROP TABLE konzern_brand_master');
        $this->addSql('DROP TABLE konzern');
    }
}
