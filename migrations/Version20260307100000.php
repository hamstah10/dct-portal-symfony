<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260307100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add kuerzel field to firma table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE firma ADD kuerzel VARCHAR(30) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE firma DROP kuerzel');
    }
}

