<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260327001000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add stage_code to portal price list items and separate existing PKW tuning options into stage1.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE portal_price_list_item ADD stage_code VARCHAR(30) DEFAULT '' NOT NULL AFTER category");
        $this->addSql('ALTER TABLE portal_price_list_item DROP INDEX uniq_portal_price_list_item_key');
        $this->addSql('UPDATE portal_price_list_item SET stage_code = \'stage1\' WHERE category = \'option\'');
        $this->addSql("ALTER TABLE portal_price_list_item ADD CONSTRAINT uniq_portal_price_list_item_key UNIQUE (price_list_id, section, category, stage_code, code)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE portal_price_list_item DROP INDEX uniq_portal_price_list_item_key');
        $this->addSql("UPDATE portal_price_list_item SET stage_code = ''");
        $this->addSql('ALTER TABLE portal_price_list_item DROP COLUMN stage_code');
        $this->addSql('ALTER TABLE portal_price_list_item ADD CONSTRAINT uniq_portal_price_list_item_key UNIQUE (price_list_id, section, category, code)');
    }
}
