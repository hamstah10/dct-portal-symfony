<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260715110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'WinOLS configuration portal assignments';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE winols_configuration_portal (configuration_id INT NOT NULL, portal_id INT NOT NULL, INDEX IDX_6B2A54B8D952DB9A (configuration_id), INDEX IDX_6B2A54B835F758B8 (portal_id), PRIMARY KEY(configuration_id, portal_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE winols_configuration_portal ADD CONSTRAINT FK_6B2A54B8D952DB9A FOREIGN KEY (configuration_id) REFERENCES winols_configuration (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE winols_configuration_portal ADD CONSTRAINT FK_6B2A54B835F758B8 FOREIGN KEY (portal_id) REFERENCES portal (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE winols_configuration_portal DROP FOREIGN KEY FK_6B2A54B8D952DB9A');
        $this->addSql('ALTER TABLE winols_configuration_portal DROP FOREIGN KEY FK_6B2A54B835F758B8');
        $this->addSql('DROP TABLE winols_configuration_portal');
    }
}
