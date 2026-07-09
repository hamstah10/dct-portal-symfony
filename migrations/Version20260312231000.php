<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312231000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Widen shop tax country column for longer labels.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_tax MODIFY country VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_tax MODIFY country VARCHAR(10) DEFAULT NULL');
    }
}
