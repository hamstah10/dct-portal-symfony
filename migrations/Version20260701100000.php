<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'WatermarkAreaConfig: add portal_key for per-portal watermark configuration';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_watermark_area_config ADD COLUMN portal_key VARCHAR(80) DEFAULT NULL AFTER area');
        $this->addSql('DROP INDEX uniq_area ON tfd_watermark_area_config');
        $this->addSql('DROP INDEX IF EXISTS UNIQ_WATERMARK_AREA ON tfd_watermark_area_config');
        $this->addSql('ALTER TABLE tfd_watermark_area_config MODIFY area VARCHAR(60) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_area_portal ON tfd_watermark_area_config (area, portal_key)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_area_portal ON tfd_watermark_area_config');
        $this->addSql('ALTER TABLE tfd_watermark_area_config DROP COLUMN portal_key');
        $this->addSql('CREATE UNIQUE INDEX uniq_area ON tfd_watermark_area_config (area)');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
