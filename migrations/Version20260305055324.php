<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305055324 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecumanufacturer CHANGE logo logo VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE images images LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_toolmanufacturer CHANGE logo logo VARCHAR(255) DEFAULT NULL, CHANGE images images LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_toolmethode CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicletype CHANGE image image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecu CHANGE image image INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecumanufacturer CHANGE logo logo INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_ecuoption CHANGE image image INT DEFAULT NULL, CHANGE images images INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_toolmanufacturer CHANGE logo logo INT DEFAULT NULL, CHANGE images images INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_toolmethode CHANGE image image INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_vehicletype CHANGE image image INT DEFAULT NULL');
    }
}
