<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tfd_textbaustein table for multilingual text snippets';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE tfd_textbaustein (
            id INT AUTO_INCREMENT NOT NULL,
            `key` VARCHAR(120) NOT NULL,
            type VARCHAR(10) NOT NULL DEFAULT 'short',
            text_de LONGTEXT DEFAULT NULL,
            text_en LONGTEXT DEFAULT NULL,
            description VARCHAR(255) DEFAULT NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            sorting INT NOT NULL DEFAULT 0,
            UNIQUE INDEX uniq_textbaustein_key (`key`),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tfd_textbaustein');
    }
}
