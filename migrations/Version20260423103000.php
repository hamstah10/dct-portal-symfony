<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260423103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ecu option hints table for imported Swiftec module notes.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_ecu_option_hint (uid INT AUTO_INCREMENT NOT NULL, module_name VARCHAR(255) NOT NULL, module_slug VARCHAR(255) DEFAULT NULL, module_url VARCHAR(1024) DEFAULT NULL, brand_name VARCHAR(255) NOT NULL, brand_slug VARCHAR(255) DEFAULT NULL, manufacturer VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, notes_de LONGTEXT DEFAULT NULL, raw LONGTEXT DEFAULT NULL, source_file VARCHAR(255) DEFAULT NULL, scraped_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', signature_hash VARCHAR(64) NOT NULL, active TINYINT(1) NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_ECU_OPTION_HINT_SIGNATURE (signature_hash), INDEX IDX_ECU_OPTION_HINT_MODULE_SLUG (module_slug), INDEX IDX_ECU_OPTION_HINT_BRAND_SLUG (brand_slug), INDEX IDX_ECU_OPTION_HINT_MANUFACTURER (manufacturer), INDEX IDX_ECU_OPTION_HINT_MODEL (model), INDEX IDX_ECU_OPTION_HINT_ACTIVE (active), PRIMARY KEY(uid)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tx_chiptuningdatabase_ecu_option_hint');
    }
}
