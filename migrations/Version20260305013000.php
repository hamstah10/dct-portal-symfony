<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305013000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add images storage field to software.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE software ADD bilder LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE software DROP bilder');
    }
}
