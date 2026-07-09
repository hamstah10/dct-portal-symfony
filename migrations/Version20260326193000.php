<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260326193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add website and company description fields to kunde';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('kunde');

        if (!$table->hasColumn('website')) {
            $table->addColumn('website', 'string', [
                'length' => 255,
                'notnull' => false,
            ]);
        }

        if (!$table->hasColumn('firmenbeschreibung')) {
            $table->addColumn('firmenbeschreibung', 'text', [
                'notnull' => false,
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('kunde');

        if ($table->hasColumn('website')) {
            $table->dropColumn('website');
        }

        if ($table->hasColumn('firmenbeschreibung')) {
            $table->dropColumn('firmenbeschreibung');
        }
    }
}

