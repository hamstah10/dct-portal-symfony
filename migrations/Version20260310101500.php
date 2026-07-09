<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310101500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds email labels and label assignment to email account configuration.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE email_label (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(120) NOT NULL, color VARCHAR(20) DEFAULT NULL, sorting INT NOT NULL, UNIQUE INDEX UNIQ_EMAIL_LABEL_NAME (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE email_konto_label (email_konto_id INT NOT NULL, email_label_id INT NOT NULL, INDEX IDX_EMAIL_KONTO_LABEL_ACCOUNT (email_konto_id), INDEX IDX_EMAIL_KONTO_LABEL_LABEL (email_label_id), PRIMARY KEY(email_konto_id, email_label_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE email_konto_label ADD CONSTRAINT FK_EMAIL_KONTO_LABEL_ACCOUNT FOREIGN KEY (email_konto_id) REFERENCES email_konto (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE email_konto_label ADD CONSTRAINT FK_EMAIL_KONTO_LABEL_LABEL FOREIGN KEY (email_label_id) REFERENCES email_label (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email_konto_label DROP FOREIGN KEY FK_EMAIL_KONTO_LABEL_ACCOUNT');
        $this->addSql('ALTER TABLE email_konto_label DROP FOREIGN KEY FK_EMAIL_KONTO_LABEL_LABEL');
        $this->addSql('DROP TABLE email_konto_label');
        $this->addSql('DROP TABLE email_label');
    }
}

