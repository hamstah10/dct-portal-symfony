<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260709051502 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Partner Wizard step/question/question_option tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tfd_partner_wizard_question (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(180) NOT NULL, help_text LONGTEXT DEFAULT NULL, answer_key VARCHAR(40) NOT NULL, question_type VARCHAR(20) NOT NULL, multi_select TINYINT NOT NULL, priority INT NOT NULL, active TINYINT NOT NULL, step_id INT NOT NULL, INDEX IDX_9D47E50C73B21E9C (step_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tfd_partner_wizard_question_option (id INT AUTO_INCREMENT NOT NULL, value VARCHAR(80) NOT NULL, title VARCHAR(140) NOT NULL, text LONGTEXT DEFAULT NULL, icon VARCHAR(500) DEFAULT NULL, numeric_value INT DEFAULT NULL, priority INT NOT NULL, active TINYINT NOT NULL, question_id INT NOT NULL, media_asset_id INT DEFAULT NULL, INDEX IDX_8765BA561E27F6BF (question_id), INDEX IDX_8765BA56ABB37F3 (media_asset_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tfd_partner_wizard_step (id INT AUTO_INCREMENT NOT NULL, step_key VARCHAR(80) NOT NULL, label VARCHAR(140) NOT NULL, type VARCHAR(20) NOT NULL, priority INT NOT NULL, active TINYINT NOT NULL, client_id INT NOT NULL, INDEX IDX_D707502A19EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE tfd_partner_wizard_question ADD CONSTRAINT FK_9D47E50C73B21E9C FOREIGN KEY (step_id) REFERENCES tfd_partner_wizard_step (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_question_option ADD CONSTRAINT FK_8765BA561E27F6BF FOREIGN KEY (question_id) REFERENCES tfd_partner_wizard_question (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_question_option ADD CONSTRAINT FK_8765BA56ABB37F3 FOREIGN KEY (media_asset_id) REFERENCES media_asset (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE tfd_partner_wizard_step ADD CONSTRAINT FK_D707502A19EB6921 FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_question DROP FOREIGN KEY FK_9D47E50C73B21E9C');
        $this->addSql('ALTER TABLE tfd_partner_wizard_question_option DROP FOREIGN KEY FK_8765BA561E27F6BF');
        $this->addSql('ALTER TABLE tfd_partner_wizard_question_option DROP FOREIGN KEY FK_8765BA56ABB37F3');
        $this->addSql('ALTER TABLE tfd_partner_wizard_step DROP FOREIGN KEY FK_D707502A19EB6921');
        $this->addSql('DROP TABLE tfd_partner_wizard_question');
        $this->addSql('DROP TABLE tfd_partner_wizard_question_option');
        $this->addSql('DROP TABLE tfd_partner_wizard_step');
    }
}
