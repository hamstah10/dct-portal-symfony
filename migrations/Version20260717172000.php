<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260717172000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add API active flag to shop products';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_product ADD api_active TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_tuningtoolshop_domain_model_product DROP api_active');
    }
}
