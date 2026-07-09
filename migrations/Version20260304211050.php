<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304211050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kunde ADD firma_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE kunde ADD CONSTRAINT FK_A213CB1505AEC11 FOREIGN KEY (firma_id) REFERENCES firma (id)');
        $this->addSql('CREATE INDEX IDX_A213CB1505AEC11 ON kunde (firma_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kunde DROP FOREIGN KEY FK_A213CB1505AEC11');
        $this->addSql('DROP INDEX IDX_A213CB1505AEC11 ON kunde');
        $this->addSql('ALTER TABLE kunde DROP firma_id');
    }
}
