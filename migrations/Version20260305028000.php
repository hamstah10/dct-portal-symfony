<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305028000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Macht software_id in abo optional, damit entweder Software oder Paket gewaehlt wird.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo CHANGE software_id software_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE abo CHANGE software_id software_id INT NOT NULL');
    }
}
