<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260328100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add payment reference column to kunde_credit_transaction for external payments';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde_credit_transaction ADD payment_reference VARCHAR(128) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde_credit_transaction DROP payment_reference');
    }
}
