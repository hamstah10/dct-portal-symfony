<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260305003000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add many-to-many relation between demo and software.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE demo_software (demo_id INT NOT NULL, software_id INT NOT NULL, INDEX IDX_5F6EE07EA0A1E361 (demo_id), INDEX IDX_5F6EE07ED7452741 (software_id), PRIMARY KEY(demo_id, software_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE demo_software ADD CONSTRAINT FK_5F6EE07EA0A1E361 FOREIGN KEY (demo_id) REFERENCES demo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE demo_software ADD CONSTRAINT FK_5F6EE07ED7452741 FOREIGN KEY (software_id) REFERENCES software (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE demo_software DROP FOREIGN KEY FK_5F6EE07EA0A1E361');
        $this->addSql('ALTER TABLE demo_software DROP FOREIGN KEY FK_5F6EE07ED7452741');
        $this->addSql('DROP TABLE demo_software');
    }
}
