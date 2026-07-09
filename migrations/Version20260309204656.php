<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260309204656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates tx_chiptuningdatabase_antriebsart with fields name and icon.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_antriebsart (uid INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT NULL, PRIMARY KEY (uid)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tx_chiptuningdatabase_antriebsart');
    }
}
