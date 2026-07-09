<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260404194000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add description column to ecu table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu ADD description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu DROP description');
    }
}
