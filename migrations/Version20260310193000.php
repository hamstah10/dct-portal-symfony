<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add lexware contact id to kunde.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde ADD lexware_contact_id VARCHAR(64) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde DROP lexware_contact_id');
    }
}
