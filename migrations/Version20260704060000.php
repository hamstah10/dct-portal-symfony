<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704060000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image_url to tfd_partner_wizard_tool';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool ADD image_url VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool DROP image_url');
    }
}
