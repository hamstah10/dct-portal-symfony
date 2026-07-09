<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260314103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create AI DALL-E image history table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ai_dalle_image (id INT AUTO_INCREMENT NOT NULL, prompt LONGTEXT NOT NULL, revised_prompt LONGTEXT DEFAULT NULL, model VARCHAR(50) NOT NULL, size VARCHAR(20) NOT NULL, quality VARCHAR(20) NOT NULL, style VARCHAR(20) NOT NULL, response_format VARCHAR(20) NOT NULL, image_path VARCHAR(255) DEFAULT NULL, image_url LONGTEXT DEFAULT NULL, metadata LONGTEXT DEFAULT NULL, error LONGTEXT DEFAULT NULL, created_at INT NOT NULL, updated_at INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ai_dalle_image');
    }
}
