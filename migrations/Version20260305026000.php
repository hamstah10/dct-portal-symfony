<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305026000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fuegt Zahlweise (monatlich/jaehrlich) in Abo hinzu.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE abo ADD zahlweise VARCHAR(20) NOT NULL DEFAULT 'monatlich'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo DROP zahlweise');
    }
}
