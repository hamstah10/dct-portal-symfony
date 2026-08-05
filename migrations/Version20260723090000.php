<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260723090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dashboard-Wunschzettel: Produkte mit Titel, Link und Bild (max. 4)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE dashboard_wishlist_item (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(2048) NOT NULL, image_url VARCHAR(2048) DEFAULT NULL, sorting INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE dashboard_wishlist_item');
    }
}
