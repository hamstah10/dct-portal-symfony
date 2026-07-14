<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260709060318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add fieldType/required columns to tfd_partner_wizard_question for contact-step form fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_question ADD field_type VARCHAR(20) DEFAULT NULL, ADD required TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_question DROP field_type, DROP required');
    }
}
