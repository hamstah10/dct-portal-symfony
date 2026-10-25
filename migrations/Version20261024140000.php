<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261024140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create formula table for formulas and if-then rules in the superadmin portal';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE formula (
                id INT AUTO_INCREMENT NOT NULL,
                formula_key VARCHAR(120) NOT NULL,
                name VARCHAR(160) NOT NULL,
                area VARCHAR(120) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                unit VARCHAR(40) DEFAULT NULL,
                variables JSON NOT NULL,
                rules JSON NOT NULL,
                active TINYINT(1) NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE INDEX UNIQ_67315881E53AEF4C (formula_key),
                INDEX IDX_FORMULA_AREA (area),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE formula');
    }
}
