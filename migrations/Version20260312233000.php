<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260312233000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change shop product operating_costs to text.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_product MODIFY operating_costs LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE tx_tuningtoolshop_domain_model_product MODIFY operating_costs DECIMAL(10,2) NOT NULL DEFAULT '0.00'");
    }
}
