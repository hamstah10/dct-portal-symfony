<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027200500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add firma field to credit_package';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE credit_package ADD firma_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE credit_package ADD CONSTRAINT FK_F5228A0F9D59E8B3 FOREIGN KEY (firma_id) REFERENCES firma (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_F5228A0F9D59E8B3 ON credit_package (firma_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE credit_package DROP FOREIGN KEY FK_F5228A0F9D59E8B3');
        $this->addSql('DROP INDEX IDX_F5228A0F9D59E8B3 ON credit_package');
        $this->addSql('ALTER TABLE credit_package DROP firma_id');
    }
}
