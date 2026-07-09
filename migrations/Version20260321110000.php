<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260321110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add portal upload jobs for customer portal workflows';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE portal_upload_job (
            id INT AUTO_INCREMENT NOT NULL,
            kunde_id INT NOT NULL,
            brand_key VARCHAR(80) NOT NULL,
            original_filename VARCHAR(255) NOT NULL,
            stored_filename VARCHAR(255) NOT NULL,
            mime_type VARCHAR(180) DEFAULT NULL,
            size_bytes INT NOT NULL,
            status VARCHAR(40) NOT NULL,
            notes LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            processed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            returned_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_388CEB28C5E27A72 (kunde_id),
            INDEX IDX_388CEB28346AF503 (brand_key),
            INDEX IDX_388CEB28BF7AF263 (status),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE portal_upload_job ADD CONSTRAINT FK_388CEB28C5E27A72 FOREIGN KEY (kunde_id) REFERENCES kunde (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE portal_upload_job');
    }
}
