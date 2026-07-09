<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305001500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add preview-images storage column to template table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE template ADD vorschaubilder LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE template DROP vorschaubilder');
    }
}
