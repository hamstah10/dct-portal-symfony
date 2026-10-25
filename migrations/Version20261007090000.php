<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261007090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow multiple active MCUs on installation hints.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_einbau_hinweis_active_ecu_mm (einbau_hinweis_id INT NOT NULL, ecu_id INT NOT NULL, INDEX IDX_EINBAU_HINWEIS_ACTIVE_ECU_MM_HINT (einbau_hinweis_id), INDEX IDX_EINBAU_HINWEIS_ACTIVE_ECU_MM_ECU (ecu_id), PRIMARY KEY(einbau_hinweis_id, ecu_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_active_ecu_mm ADD CONSTRAINT FK_EINBAU_HINWEIS_ACTIVE_ECU_MM_HINT FOREIGN KEY (einbau_hinweis_id) REFERENCES tx_chiptuningdatabase_einbau_hinweis (uid) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_active_ecu_mm ADD CONSTRAINT FK_EINBAU_HINWEIS_ACTIVE_ECU_MM_ECU FOREIGN KEY (ecu_id) REFERENCES tx_chiptuningdatabase_domain_model_ecu (uid) ON DELETE CASCADE');
        $this->addSql('INSERT IGNORE INTO tx_chiptuningdatabase_einbau_hinweis_active_ecu_mm (einbau_hinweis_id, ecu_id) SELECT uid, active_ecu_id FROM tx_chiptuningdatabase_einbau_hinweis WHERE active_ecu_id IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_active_ecu_mm DROP FOREIGN KEY FK_EINBAU_HINWEIS_ACTIVE_ECU_MM_HINT');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis_active_ecu_mm DROP FOREIGN KEY FK_EINBAU_HINWEIS_ACTIVE_ECU_MM_ECU');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_einbau_hinweis_active_ecu_mm');
    }
}
