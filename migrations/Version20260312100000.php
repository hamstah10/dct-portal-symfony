<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add DTC catalog tables for categories and codes.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tx_tuningdtccodes_domain_model_category (uid INT NOT NULL, pid INT NOT NULL, tstamp INT NOT NULL, crdate INT NOT NULL, deleted TINYINT(1) NOT NULL, hidden TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, buchstabe VARCHAR(10) NOT NULL, beschreibung LONGTEXT DEFAULT NULL, PRIMARY KEY(uid)) DEFAULT CHARSET=utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tx_tuningdtccodes_domain_model_dtccode (uid INT NOT NULL, pid INT NOT NULL, tstamp INT NOT NULL, crdate INT NOT NULL, deleted TINYINT(1) NOT NULL, hidden TINYINT(1) NOT NULL, fehlercode VARCHAR(20) NOT NULL, beschreibung LONGTEXT DEFAULT NULL, category INT NOT NULL, INDEX IDX_DTC_CODE_CATEGORY (category), INDEX IDX_DTC_CODE_FEHLERCODE (fehlercode), PRIMARY KEY(uid)) DEFAULT CHARSET=utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tx_tuningdtccodes_domain_model_dtccode ADD CONSTRAINT FK_DTC_CODE_CATEGORY FOREIGN KEY (category) REFERENCES tx_tuningdtccodes_domain_model_category (uid) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_tuningdtccodes_domain_model_dtccode DROP FOREIGN KEY FK_DTC_CODE_CATEGORY');
        $this->addSql('DROP TABLE tx_tuningdtccodes_domain_model_dtccode');
        $this->addSql('DROP TABLE tx_tuningdtccodes_domain_model_category');
    }
}
