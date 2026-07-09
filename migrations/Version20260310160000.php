<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create VIN decoder cache table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE vin_decoder_cache (id INT AUTO_INCREMENT NOT NULL, vin VARCHAR(20) NOT NULL, payload JSON NOT NULL, provider VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_1D9B0F3CB7597C1D (vin), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE vin_decoder_cache');
    }
}
