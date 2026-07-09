<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312153000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add reversal reference for customer credit transactions.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde_credit_transaction ADD reversal_of_id INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_KCT_REVERSAL_OF ON kunde_credit_transaction (reversal_of_id)');
        $this->addSql('ALTER TABLE kunde_credit_transaction ADD CONSTRAINT FK_KCT_REVERSAL_OF FOREIGN KEY (reversal_of_id) REFERENCES kunde_credit_transaction (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde_credit_transaction DROP FOREIGN KEY FK_KCT_REVERSAL_OF');
        $this->addSql('DROP INDEX IDX_KCT_REVERSAL_OF ON kunde_credit_transaction');
        $this->addSql('ALTER TABLE kunde_credit_transaction DROP reversal_of_id');
    }
}
