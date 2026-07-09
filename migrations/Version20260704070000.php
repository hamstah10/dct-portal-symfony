<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704070000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tfd_partner_wizard_combination with tool m:n link';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tfd_partner_wizard_combination (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, name VARCHAR(140) NOT NULL, vehicle_categories JSON NOT NULL, services JSON NOT NULL, priority INT NOT NULL, active TINYINT(1) NOT NULL, INDEX IDX_TFD_PW_COMBINATION_CLIENT (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tfd_partner_wizard_combination_tool (combination_id INT NOT NULL, tool_id INT NOT NULL, INDEX IDX_TFD_PW_COMB_TOOL_COMBINATION (combination_id), INDEX IDX_TFD_PW_COMB_TOOL_TOOL (tool_id), PRIMARY KEY(combination_id, tool_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination ADD CONSTRAINT FK_TFD_PW_COMBINATION_CLIENT FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination_tool ADD CONSTRAINT FK_TFD_PW_COMB_TOOL_COMBINATION FOREIGN KEY (combination_id) REFERENCES tfd_partner_wizard_combination (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination_tool ADD CONSTRAINT FK_TFD_PW_COMB_TOOL_TOOL FOREIGN KEY (tool_id) REFERENCES tfd_partner_wizard_tool (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination_tool DROP FOREIGN KEY FK_TFD_PW_COMB_TOOL_COMBINATION');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination_tool DROP FOREIGN KEY FK_TFD_PW_COMB_TOOL_TOOL');
        $this->addSql('ALTER TABLE tfd_partner_wizard_combination DROP FOREIGN KEY FK_TFD_PW_COMBINATION_CLIENT');
        $this->addSql('DROP TABLE tfd_partner_wizard_combination_tool');
        $this->addSql('DROP TABLE tfd_partner_wizard_combination');
    }
}
