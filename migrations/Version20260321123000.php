<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260321123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add password reset tokens for customer portal accounts';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE portal_password_reset_token (
            id INT AUTO_INCREMENT NOT NULL,
            kunde_id INT NOT NULL,
            brand_key VARCHAR(80) NOT NULL,
            token_hash VARCHAR(128) NOT NULL,
            requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_95B8BFAFC5E27A72 (kunde_id),
            UNIQUE INDEX UNIQ_95B8BFAF9C6228A (token_hash),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE portal_password_reset_token ADD CONSTRAINT FK_95B8BFAFC5E27A72 FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE portal_password_reset_token');
    }
}
