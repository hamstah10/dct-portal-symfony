<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261026200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Letzter Login am Kunden (last_login_at) ergänzen';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde ADD last_login_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde DROP last_login_at');
    }
}
