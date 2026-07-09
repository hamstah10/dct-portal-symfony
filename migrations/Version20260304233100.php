<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304233100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extend abo_auswahl with laufzeit/preis/art and add many-to-many relation to software.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE abo_auswahl ADD laufzeit VARCHAR(80) DEFAULT NULL, ADD preis NUMERIC(10, 2) DEFAULT NULL, ADD art VARCHAR(20) NOT NULL DEFAULT 'aktiv'");
        $this->addSql('CREATE TABLE abo_auswahl_software (abo_auswahl_id INT NOT NULL, software_id INT NOT NULL, INDEX IDX_9D08EFA15D69D08F (abo_auswahl_id), INDEX IDX_9D08EFA1D7452741 (software_id), PRIMARY KEY(abo_auswahl_id, software_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE abo_auswahl_software ADD CONSTRAINT FK_9D08EFA15D69D08F FOREIGN KEY (abo_auswahl_id) REFERENCES abo_auswahl (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE abo_auswahl_software ADD CONSTRAINT FK_9D08EFA1D7452741 FOREIGN KEY (software_id) REFERENCES software (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo_auswahl_software DROP FOREIGN KEY FK_9D08EFA15D69D08F');
        $this->addSql('ALTER TABLE abo_auswahl_software DROP FOREIGN KEY FK_9D08EFA1D7452741');
        $this->addSql('DROP TABLE abo_auswahl_software');
        $this->addSql('ALTER TABLE abo_auswahl DROP laufzeit, DROP preis, DROP art');
    }
}
