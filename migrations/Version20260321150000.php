<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260321150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enable order module for the chiptuningfile portal';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE portal
            SET enabled_modules = JSON_ARRAY_APPEND(enabled_modules, '$', 'order')
            WHERE portal_key = 'chiptuningfile'
              AND JSON_CONTAINS(enabled_modules, JSON_QUOTE('order'), '$') = 0
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE portal
            SET enabled_modules = JSON_REMOVE(
                enabled_modules,
                JSON_UNQUOTE(JSON_SEARCH(enabled_modules, 'one', 'order'))
            )
            WHERE portal_key = 'chiptuningfile'
              AND JSON_SEARCH(enabled_modules, 'one', 'order') IS NOT NULL
        SQL);
    }
}
