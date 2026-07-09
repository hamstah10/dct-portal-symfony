<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312184500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add pricing and payment metadata to customer credit transactions.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde_credit_transaction ADD payment_method VARCHAR(40) DEFAULT NULL, ADD price_net NUMERIC(10, 2) DEFAULT NULL, ADD price_gross NUMERIC(10, 2) DEFAULT NULL, ADD tax_rate NUMERIC(5, 2) DEFAULT NULL, ADD payment_fee NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde_credit_transaction DROP payment_method, DROP price_net, DROP price_gross, DROP tax_rate, DROP payment_fee');
    }
}
