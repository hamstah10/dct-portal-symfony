<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261024130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create brand_assistant_qa table to store questions and answers of the brand assistant';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE brand_assistant_qa (
                id INT AUTO_INCREMENT NOT NULL,
                question LONGTEXT NOT NULL,
                answer LONGTEXT NOT NULL,
                model VARCHAR(120) NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX IDX_BRAND_ASSISTANT_QA_CREATED (created_at),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE brand_assistant_qa');
    }
}
