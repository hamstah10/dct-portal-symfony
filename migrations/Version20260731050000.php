<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260731050000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add superadmin_vehicle_compare_list table for saved vehicle comparison lists.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE superadmin_vehicle_compare_list (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(190) NOT NULL, vehicles JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_SUPERADMIN_COMPARE_LIST_USER (user_id), INDEX IDX_SUPERADMIN_COMPARE_LIST_UPDATED_AT (updated_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE superadmin_vehicle_compare_list ADD CONSTRAINT FK_SUPERADMIN_COMPARE_LIST_USER FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE superadmin_vehicle_compare_list DROP FOREIGN KEY FK_SUPERADMIN_COMPARE_LIST_USER');
        $this->addSql('DROP TABLE superadmin_vehicle_compare_list');
    }
}
