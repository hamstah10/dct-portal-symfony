<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260707060000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add margin_percent column to tfd_watermark_preset for percentage-based edge margin.';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_watermark_preset ADD margin_percent NUMERIC(5, 2) NOT NULL DEFAULT 3.00');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_watermark_preset DROP margin_percent');
    }
}
