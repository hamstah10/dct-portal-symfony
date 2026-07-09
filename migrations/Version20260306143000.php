<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306143000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add media folders with nested hierarchy and connect media assets to folders.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE media_folder (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, slug VARCHAR(255) DEFAULT NULL, active TINYINT(1) NOT NULL, sorting INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_MEDIA_FOLDER_PARENT (parent_id), INDEX IDX_MEDIA_FOLDER_ACTIVE (active), INDEX IDX_MEDIA_FOLDER_SORTING (sorting), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE media_folder ADD CONSTRAINT FK_MEDIA_FOLDER_PARENT FOREIGN KEY (parent_id) REFERENCES media_folder (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE media_asset ADD folder_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_MEDIA_ASSET_FOLDER ON media_asset (folder_id)');
        $this->addSql('ALTER TABLE media_asset ADD CONSTRAINT FK_MEDIA_ASSET_FOLDER FOREIGN KEY (folder_id) REFERENCES media_folder (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_asset DROP FOREIGN KEY FK_MEDIA_ASSET_FOLDER');
        $this->addSql('DROP INDEX IDX_MEDIA_ASSET_FOLDER ON media_asset');
        $this->addSql('ALTER TABLE media_asset DROP folder_id');
        $this->addSql('ALTER TABLE media_folder DROP FOREIGN KEY FK_MEDIA_FOLDER_PARENT');
        $this->addSql('DROP TABLE media_folder');
    }
}
