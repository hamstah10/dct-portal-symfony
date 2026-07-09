<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260326195500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add dyno and tool fields to kunde for portal/superadmin sync';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('kunde');

        if (!$table->hasColumn('has_dyno')) {
            $table->addColumn('has_dyno', 'boolean', [
                'default' => false,
            ]);
        }

        if (!$table->hasColumn('tuning_tools')) {
            $table->addColumn('tuning_tools', 'json', [
                'notnull' => false,
            ]);
        }

        if (!$table->hasColumn('diagnose_tools')) {
            $table->addColumn('diagnose_tools', 'json', [
                'notnull' => false,
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('kunde');

        if ($table->hasColumn('diagnose_tools')) {
            $table->dropColumn('diagnose_tools');
        }

        if ($table->hasColumn('tuning_tools')) {
            $table->dropColumn('tuning_tools');
        }

        if ($table->hasColumn('has_dyno')) {
            $table->dropColumn('has_dyno');
        }
    }
}

