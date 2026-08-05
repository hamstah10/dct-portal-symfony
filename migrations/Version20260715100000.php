<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260715100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'WinOLS configuration and rule tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE winols_configuration (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(190) NOT NULL, api_base_url VARCHAR(255) NOT NULL, api_token VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE winols_configuration_rule (id INT AUTO_INCREMENT NOT NULL, configuration_id INT NOT NULL, name VARCHAR(190) NOT NULL, allowed_modes JSON NOT NULL, allowed_extensions LONGTEXT DEFAULT NULL, active TINYINT(1) NOT NULL, sort_order INT NOT NULL, INDEX IDX_F7E8E3A5140F4E3F (configuration_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE winols_configuration_rule_tool_manufacturer (rule_id INT NOT NULL, tool_manufacturer_id INT NOT NULL, INDEX IDX_1FBC7FEF66B97342 (rule_id), INDEX IDX_1FBC7FEF919EF532 (tool_manufacturer_id), PRIMARY KEY(rule_id, tool_manufacturer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE winols_configuration_rule ADD CONSTRAINT FK_F7E8E3A5140F4E3F FOREIGN KEY (configuration_id) REFERENCES winols_configuration (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE winols_configuration_rule_tool_manufacturer ADD CONSTRAINT FK_1FBC7FEF66B97342 FOREIGN KEY (rule_id) REFERENCES winols_configuration_rule (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE winols_configuration_rule_tool_manufacturer ADD CONSTRAINT FK_1FBC7FEF919EF532 FOREIGN KEY (tool_manufacturer_id) REFERENCES tx_chiptuningdatabase_domain_model_toolmanufacturer (uid) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE winols_configuration_rule_tool_manufacturer DROP FOREIGN KEY FK_1FBC7FEF66B97342');
        $this->addSql('ALTER TABLE winols_configuration_rule_tool_manufacturer DROP FOREIGN KEY FK_1FBC7FEF919EF532');
        $this->addSql('ALTER TABLE winols_configuration_rule DROP FOREIGN KEY FK_F7E8E3A5140F4E3F');
        $this->addSql('DROP TABLE winols_configuration_rule_tool_manufacturer');
        $this->addSql('DROP TABLE winols_configuration_rule');
        $this->addSql('DROP TABLE winols_configuration');
    }
}
