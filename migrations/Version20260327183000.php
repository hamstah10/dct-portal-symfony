<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260327183000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add portal payment method assignments.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE portal_zahlungsart (portal_id INT NOT NULL, zahlungsart_id INT NOT NULL, INDEX IDX_D6B45D0361233D7C (portal_id), INDEX IDX_D6B45D03E02A7284 (zahlungsart_id), PRIMARY KEY(portal_id, zahlungsart_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE portal_zahlungsart ADD CONSTRAINT FK_D6B45D0361233D7C FOREIGN KEY (portal_id) REFERENCES portal (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE portal_zahlungsart ADD CONSTRAINT FK_D6B45D03E02A7284 FOREIGN KEY (zahlungsart_id) REFERENCES zahlungsart (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE portal_zahlungsart');
    }
}
