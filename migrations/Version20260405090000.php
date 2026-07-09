<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260405090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add newsletter flag to crm_lead';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE crm_lead ADD newsletter TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE crm_lead DROP newsletter');
    }
}
