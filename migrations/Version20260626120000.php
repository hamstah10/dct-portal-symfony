<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Kopiert alle Preislisten-Positionen von portal_chiptuningfile_default (id=1) nach portal_no1tuningfiles_default (id=2)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            INSERT IGNORE INTO portal_price_list_item
                (price_list_id, section, category, stage_code, code, name, description, credits, included, sort_order, active)
            SELECT
                2, section, category, stage_code, code, name, description, credits, included, sort_order, active
            FROM portal_price_list_item
            WHERE price_list_id = 1
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DELETE FROM portal_price_list_item WHERE price_list_id = 2
        SQL);
    }
}
