<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027195000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add lexwareArticleNumber field to credit_package';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE credit_package ADD lexware_article_number VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE credit_package DROP lexware_article_number');
    }
}
