<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add admin page access rules for menu visibility.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE admin_page_access (id INT AUTO_INCREMENT NOT NULL, page_key VARCHAR(120) NOT NULL, enabled BOOLEAN NOT NULL DEFAULT TRUE, UNIQUE INDEX UNIQ_ADMIN_PAGE_ACCESS_PAGE_KEY (page_key), PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE admin_page_access_user_group (admin_page_access_id INT NOT NULL, user_group_id INT NOT NULL, INDEX IDX_ADMIN_PAGE_ACCESS_USER_GROUP_ACCESS (admin_page_access_id), INDEX IDX_ADMIN_PAGE_ACCESS_USER_GROUP_GROUP (user_group_id), PRIMARY KEY(admin_page_access_id, user_group_id))');
        $this->addSql('ALTER TABLE admin_page_access_user_group ADD CONSTRAINT FK_ADMIN_PAGE_ACCESS_USER_GROUP_ACCESS FOREIGN KEY (admin_page_access_id) REFERENCES admin_page_access (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE admin_page_access_user_group ADD CONSTRAINT FK_ADMIN_PAGE_ACCESS_USER_GROUP_GROUP FOREIGN KEY (user_group_id) REFERENCES user_group (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE admin_page_access_user_group');
        $this->addSql('DROP TABLE admin_page_access');
    }
}
