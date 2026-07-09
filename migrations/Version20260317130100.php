<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260317130100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make quality and style nullable in ai_dalle_image table for DALLE-2 support';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ai_dalle_image MODIFY quality VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE ai_dalle_image MODIFY style VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ai_dalle_image MODIFY quality VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE ai_dalle_image MODIFY style VARCHAR(255) NOT NULL');
    }
}
