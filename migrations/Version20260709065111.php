<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260709065111 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add dependsOnAnswerKey/dependsOnValue columns to tfd_partner_wizard_question for conditional question visibility';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_question ADD depends_on_answer_key VARCHAR(40) DEFAULT NULL, ADD depends_on_value VARCHAR(120) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_question DROP depends_on_answer_key, DROP depends_on_value');
    }
}
