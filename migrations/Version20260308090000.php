<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add gearbox relation to vehicle engines.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_domain_model_vehicleengine_gearbox_mm (uid_local INT NOT NULL, uid_foreign INT NOT NULL, INDEX IDX_667C42F55C4C8485 (uid_local), INDEX IDX_667C42F51D17607B (uid_foreign), PRIMARY KEY (uid_local, uid_foreign)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_vehicleengine_gearbox_mm ADD CONSTRAINT FK_667C42F55C4C8485 FOREIGN KEY (uid_local) REFERENCES tx_chiptuningdatabase_vehicleengine (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_vehicleengine_gearbox_mm ADD CONSTRAINT FK_667C42F51D17607B FOREIGN KEY (uid_foreign) REFERENCES tx_chiptuningdatabase_gearbox (uid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_vehicleengine_gearbox_mm DROP FOREIGN KEY FK_667C42F55C4C8485');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_vehicleengine_gearbox_mm DROP FOREIGN KEY FK_667C42F51D17607B');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_domain_model_vehicleengine_gearbox_mm');
    }
}

