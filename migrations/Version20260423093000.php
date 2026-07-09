<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260423093000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add active flag to brand master records.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master ADD active TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('CREATE INDEX IDX_BRAND_MASTER_ACTIVE ON tx_chiptuningdatabase_brand_master (active)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_BRAND_MASTER_ACTIVE ON tx_chiptuningdatabase_brand_master');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_brand_master DROP active');
    }
}
