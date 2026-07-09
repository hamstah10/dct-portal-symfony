<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260326234500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add theme preset selection to portal';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE portal ADD theme_preset VARCHAR(40) NOT NULL DEFAULT 'original'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal DROP theme_preset');
    }
}
