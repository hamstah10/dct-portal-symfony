<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261026210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Kundenstammdaten ergänzen: Anrede, Mobil, Sprache, Kundentyp, Preisgruppe, Rabatt, Steuersatz, IBAN, Zahlungsziel';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde ADD anrede VARCHAR(255) DEFAULT NULL, ADD mobil VARCHAR(255) DEFAULT NULL, ADD sprache VARCHAR(255) DEFAULT NULL, ADD kundentyp VARCHAR(255) DEFAULT NULL, ADD preisgruppe VARCHAR(255) DEFAULT NULL, ADD rabatt VARCHAR(255) DEFAULT NULL, ADD steuersatz VARCHAR(255) DEFAULT NULL, ADD iban VARCHAR(255) DEFAULT NULL, ADD zahlungsziel VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kunde DROP anrede, DROP mobil, DROP sprache, DROP kundentyp, DROP preisgruppe, DROP rabatt, DROP steuersatz, DROP iban, DROP zahlungsziel');
    }
}
