<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704135000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Clean up scraped junk data: strip "Stay tuned! Recently Ordered" suffix from engine names and clear stage descriptions that only contain a source URL.';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "UPDATE tx_chiptuningdatabase_vehicleengine SET name = TRIM(TRAILING ' ' FROM SUBSTRING(name, 1, LENGTH(name) - LENGTH('Stay tuned! Recently Ordered'))) WHERE name LIKE '%Stay tuned! Recently Ordered'"
        );
        $this->addSql(
            "UPDATE tx_chiptuningdatabase_vehiclestage SET description = NULL WHERE description LIKE 'http%'"
        );
    }

    public function down(Schema $schema): void
    {
        // Data cleanup is not reversible.
    }
}
