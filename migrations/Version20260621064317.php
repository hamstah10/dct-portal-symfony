<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260621064317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add annotations JSON column to media_asset';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_asset ADD annotations JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE media_asset DROP annotations');
    }
}
