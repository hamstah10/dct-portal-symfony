<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260404170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add scraper_import_log table to track JSON imports';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE scraper_import_log (
  id INT AUTO_INCREMENT NOT NULL,
  vehicle_data_pool INT DEFAULT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(1024) NOT NULL,
  status VARCHAR(50) NOT NULL,
  message LONGTEXT DEFAULT NULL,
  brand_summary LONGTEXT DEFAULT NULL,
  brand_count INT NOT NULL,
  type VARCHAR(64) DEFAULT NULL,
  created_at DATETIME NOT NULL,
  INDEX IDX_SCRAPER_IMPORT_POOL (vehicle_data_pool),
  PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;
SQL);

        $this->addSql('ALTER TABLE scraper_import_log ADD CONSTRAINT FK_SCRAPER_IMPORT_POOL FOREIGN KEY (vehicle_data_pool) REFERENCES tx_chiptuningdatabase_vehicle_data_pool (uid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE scraper_import_log');
    }
}
