<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261022150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add active flag to tool methods (inactive methods are hidden in frontends)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_toolmethode ADD active TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_toolmethode DROP active');
    }
}
