<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260731070000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add configurable links for the superadmin operations dashboard.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE superadmin_dashboard_link (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(120) NOT NULL, url VARCHAR(2048) NOT NULL, description VARCHAR(255) DEFAULT NULL, category VARCHAR(80) DEFAULT NULL, icon_name VARCHAR(80) DEFAULT NULL, sort_order INT NOT NULL, active TINYINT(1) NOT NULL, open_in_new_tab TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_SUPERADMIN_DASHBOARD_LINK_ACTIVE_SORT (active, sort_order), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE superadmin_dashboard_link');
    }
}
