<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260314111500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add count/aspect and image arrays to AI DALL-E history.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ai_dalle_image ADD image_count INT NOT NULL DEFAULT 1, ADD aspect_ratio VARCHAR(20) NOT NULL DEFAULT \'square\', ADD image_paths LONGTEXT DEFAULT NULL, ADD image_urls LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ai_dalle_image DROP image_count, DROP aspect_ratio, DROP image_paths, DROP image_urls');
    }
}
