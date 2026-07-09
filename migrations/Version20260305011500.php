<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305011500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add multi-select options field to software.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE software ADD optionen JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE software DROP optionen');
    }
}
