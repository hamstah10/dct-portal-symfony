<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add lexware_article_id to credit_package';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE credit_package ADD lexware_article_id VARCHAR(64) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE credit_package DROP COLUMN lexware_article_id');
    }
}
