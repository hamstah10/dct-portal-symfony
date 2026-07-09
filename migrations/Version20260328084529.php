<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260328084529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optional public and secret keys to ApiToken for Stripe';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE api_token ADD public_key VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE api_token ADD secret_key VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE api_token DROP public_key');
        $this->addSql('ALTER TABLE api_token DROP secret_key');
    }
}
