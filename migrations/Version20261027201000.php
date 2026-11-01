<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261027201000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert firma field to firmas many-to-many';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE credit_package_firma (credit_package_id INT NOT NULL, firma_id INT NOT NULL, PRIMARY KEY(credit_package_id, firma_id), INDEX IDX_E8C5B6A9B3D0B02C (credit_package_id), INDEX IDX_E8C5B6A99D59E8B3 (firma_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE credit_package_firma ADD CONSTRAINT FK_E8C5B6A9B3D0B02C FOREIGN KEY (credit_package_id) REFERENCES credit_package (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE credit_package_firma ADD CONSTRAINT FK_E8C5B6A99D59E8B3 FOREIGN KEY (firma_id) REFERENCES firma (id) ON DELETE CASCADE');

        // Migrate existing data
        $this->addSql('INSERT INTO credit_package_firma (credit_package_id, firma_id) SELECT id, firma_id FROM credit_package WHERE firma_id IS NOT NULL');

        $this->addSql('ALTER TABLE credit_package DROP FOREIGN KEY FK_F5228A0F9D59E8B3');
        $this->addSql('DROP INDEX IDX_F5228A0F9D59E8B3 ON credit_package');
        $this->addSql('ALTER TABLE credit_package DROP firma_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE credit_package ADD firma_id INT DEFAULT NULL');
        $this->addSql('UPDATE credit_package cp SET firma_id = (SELECT firma_id FROM credit_package_firma cpf WHERE cpf.credit_package_id = cp.id LIMIT 1)');
        $this->addSql('ALTER TABLE credit_package ADD CONSTRAINT FK_F5228A0F9D59E8B3 FOREIGN KEY (firma_id) REFERENCES firma (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_F5228A0F9D59E8B3 ON credit_package (firma_id)');

        $this->addSql('DROP TABLE credit_package_firma');
    }
}
