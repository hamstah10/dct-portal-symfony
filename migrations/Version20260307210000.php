<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260307210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ECU manufacturer relation to gearbox.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox ADD manufacturer INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_94FFA4A93D0AE6DC ON tx_chiptuningdatabase_gearbox (manufacturer)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox ADD CONSTRAINT FK_94FFA4A93D0AE6DC FOREIGN KEY (manufacturer) REFERENCES tx_chiptuningdatabase_domain_model_ecumanufacturer (uid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox DROP FOREIGN KEY FK_94FFA4A93D0AE6DC');
        $this->addSql('DROP INDEX IDX_94FFA4A93D0AE6DC ON tx_chiptuningdatabase_gearbox');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_gearbox DROP manufacturer');
    }
}

