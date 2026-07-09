<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305000500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extend demo with link, preview images and customer relation.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demo ADD kunde_id INT DEFAULT NULL, ADD link VARCHAR(255) DEFAULT NULL, ADD vorschaubilder LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE demo ADD CONSTRAINT FK_C22CF20D9395C3F3 FOREIGN KEY (kunde_id) REFERENCES kunde (id)');
        $this->addSql('CREATE INDEX IDX_C22CF20D9395C3F3 ON demo (kunde_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demo DROP FOREIGN KEY FK_C22CF20D9395C3F3');
        $this->addSql('DROP INDEX IDX_C22CF20D9395C3F3 ON demo');
        $this->addSql('ALTER TABLE demo DROP kunde_id, DROP link, DROP vorschaubilder');
    }
}
