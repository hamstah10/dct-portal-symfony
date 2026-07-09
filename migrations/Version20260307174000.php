<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260307174000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add gesperrt flag to kunde table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde ADD gesperrt TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde DROP gesperrt');
    }
}

