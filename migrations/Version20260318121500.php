<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318121500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add task metadata fields to project todo and idea cards';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project_todo_card
            ADD task_code VARCHAR(80) DEFAULT NULL,
            ADD assigned_to VARCHAR(255) DEFAULT NULL,
            ADD target_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            ADD tags JSON DEFAULT NULL,
            ADD images JSON DEFAULT NULL');

        $this->addSql('ALTER TABLE project_idea_card
            ADD task_code VARCHAR(80) DEFAULT NULL,
            ADD assigned_to VARCHAR(255) DEFAULT NULL,
            ADD target_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            ADD tags JSON DEFAULT NULL,
            ADD images JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project_idea_card
            DROP task_code,
            DROP assigned_to,
            DROP target_date,
            DROP tags,
            DROP images');

        $this->addSql('ALTER TABLE project_todo_card
            DROP task_code,
            DROP assigned_to,
            DROP target_date,
            DROP tags,
            DROP images');
    }
}
