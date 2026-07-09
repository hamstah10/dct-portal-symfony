<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310131000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add brevo_synced_at to kunde.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE kunde ADD brevo_synced_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde DROP brevo_synced_at');
    }
}
