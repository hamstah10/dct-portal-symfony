<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310124500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds user groups and links users to groups.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, roles JSON NOT NULL, sorting INT NOT NULL, UNIQUE INDEX UNIQ_USER_GROUP_NAME (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user ADD gruppe_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_USER_GRUPPE FOREIGN KEY (gruppe_id) REFERENCES user_group (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_USER_GRUPPE ON user (gruppe_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_USER_GRUPPE');
        $this->addSql('DROP INDEX IDX_USER_GRUPPE ON user');
        $this->addSql('ALTER TABLE user DROP gruppe_id');
        $this->addSql('DROP TABLE user_group');
    }
}
