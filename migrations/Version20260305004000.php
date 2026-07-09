<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305004000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add checkbox field eigene_website to demo.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demo ADD eigene_website TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demo DROP eigene_website');
    }
}
