<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260618052900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add active ECU relation to installation hints.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis ADD active_ecu_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_EINBAU_HINWEIS_ACTIVE_ECU ON tx_chiptuningdatabase_einbau_hinweis (active_ecu_id)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis ADD CONSTRAINT FK_EINBAU_HINWEIS_ACTIVE_ECU FOREIGN KEY (active_ecu_id) REFERENCES tx_chiptuningdatabase_domain_model_ecu (uid) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis DROP FOREIGN KEY FK_EINBAU_HINWEIS_ACTIVE_ECU');
        $this->addSql('DROP INDEX IDX_EINBAU_HINWEIS_ACTIVE_ECU ON tx_chiptuningdatabase_einbau_hinweis');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_einbau_hinweis DROP active_ecu_id');
    }
}
