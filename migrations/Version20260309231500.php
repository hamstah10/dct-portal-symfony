<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260309231500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds customer credits balance and credit transaction history table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde ADD credits INT NOT NULL DEFAULT 0');
        $this->addSql('CREATE TABLE kunde_credit_transaction (id INT AUTO_INCREMENT NOT NULL, kunde_id INT NOT NULL, created_by_id INT DEFAULT NULL, amount INT NOT NULL, transaction_type VARCHAR(40) NOT NULL, description LONGTEXT DEFAULT NULL, balance_before INT NOT NULL, balance_after INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_KCT_KUNDE (kunde_id), INDEX IDX_KCT_CREATED_BY (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE kunde_credit_transaction ADD CONSTRAINT FK_KCT_KUNDE FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE kunde_credit_transaction ADD CONSTRAINT FK_KCT_CREATED_BY FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde_credit_transaction DROP FOREIGN KEY FK_KCT_KUNDE');
        $this->addSql('ALTER TABLE kunde_credit_transaction DROP FOREIGN KEY FK_KCT_CREATED_BY');
        $this->addSql('DROP TABLE kunde_credit_transaction');
        $this->addSql('ALTER TABLE kunde DROP credits');
    }
}
