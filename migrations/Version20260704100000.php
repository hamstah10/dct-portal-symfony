<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow Partner Wizard clients to choose the existing tool manufacturers shown in the current-tool question';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tfd_partner_wizard_client_toolmanufacturer (client_id INT NOT NULL, tool_manufacturer_id INT NOT NULL, INDEX IDX_TFD_PW_CLIENT_TOOL_CLIENT (client_id), INDEX IDX_TFD_PW_CLIENT_TOOL_TOOL (tool_manufacturer_id), PRIMARY KEY(client_id, tool_manufacturer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_toolmanufacturer ADD CONSTRAINT FK_TFD_PW_CLIENT_TOOL_CLIENT FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_toolmanufacturer ADD CONSTRAINT FK_TFD_PW_CLIENT_TOOL_TOOL FOREIGN KEY (tool_manufacturer_id) REFERENCES tx_chiptuningdatabase_domain_model_toolmanufacturer (uid) ON DELETE CASCADE');
        $this->addSql('INSERT IGNORE INTO tfd_partner_wizard_client_toolmanufacturer (client_id, tool_manufacturer_id) SELECT c.id, t.uid FROM tfd_partner_wizard_client c CROSS JOIN tx_chiptuningdatabase_domain_model_toolmanufacturer t WHERE t.name <> ""');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_toolmanufacturer DROP FOREIGN KEY FK_TFD_PW_CLIENT_TOOL_CLIENT');
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_toolmanufacturer DROP FOREIGN KEY FK_TFD_PW_CLIENT_TOOL_TOOL');
        $this->addSql('DROP TABLE tfd_partner_wizard_client_toolmanufacturer');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
