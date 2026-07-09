<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704120500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add persisted superadmin remembered vehicles.';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE superadmin_remembered_vehicle (id INT AUTO_INCREMENT NOT NULL, engine_id INT NOT NULL, label VARCHAR(190) NOT NULL, subline VARCHAR(255) NOT NULL, selection JSON NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_SUPERADMIN_REMEMBERED_UPDATED_AT (updated_at), UNIQUE INDEX UNIQ_SUPERADMIN_REMEMBERED_ENGINE (engine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE superadmin_remembered_vehicle');
    }
}
