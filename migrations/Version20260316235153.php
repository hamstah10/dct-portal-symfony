<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260316235153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE marketing_category ADD icon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE marketing_category ADD CONSTRAINT FK_7BE06A2B54B9D732 FOREIGN KEY (icon_id) REFERENCES media_asset (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_7BE06A2B54B9D732 ON marketing_category (icon_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE marketing_category DROP FOREIGN KEY FK_7BE06A2B54B9D732');
        $this->addSql('DROP INDEX IDX_7BE06A2B54B9D732 ON marketing_category');
        $this->addSql('ALTER TABLE marketing_category DROP icon_id');
    }
}
