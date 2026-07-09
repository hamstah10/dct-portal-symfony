<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306132000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create media_asset table for generic media management in admin.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE media_asset (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, media_type VARCHAR(30) NOT NULL, mime_type VARCHAR(255) DEFAULT NULL, file_size INT DEFAULT NULL, active TINYINT(1) NOT NULL, sorting INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_70D929DCAFD4D3A5 (media_type), INDEX IDX_70D929DCA24DAD6 (active), INDEX IDX_70D929D0D38A89 (sorting), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE media_asset');
    }
}
