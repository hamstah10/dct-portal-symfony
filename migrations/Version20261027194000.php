<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027194000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reset lexwareArticleId in credit_package for fresh Lexoffice sync';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE credit_package SET lexware_article_id = NULL');
    }

    public function down(Schema $schema): void
    {
        // No data to restore
    }
}
