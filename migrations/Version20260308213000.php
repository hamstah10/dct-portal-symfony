<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308213000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds website sniffer configuration, run log and parsed page records.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
CREATE TABLE website_sniffer (
    id INT AUTO_INCREMENT NOT NULL,
    name VARCHAR(150) NOT NULL,
    directory_path VARCHAR(1024) NOT NULL,
    include_subdirectories TINYINT(1) NOT NULL,
    active TINYINT(1) NOT NULL,
    description LONGTEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    last_run_at DATETIME DEFAULT NULL,
    last_run_status VARCHAR(20) DEFAULT NULL,
    last_run_pages_count INT DEFAULT NULL,
    INDEX IDX_WEBSITE_SNIFFER_ACTIVE (active),
    INDEX IDX_WEBSITE_SNIFFER_LAST_RUN_AT (last_run_at),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4
SQL);

        $this->addSql(<<<'SQL'
CREATE TABLE website_sniffer_run (
    id INT AUTO_INCREMENT NOT NULL,
    sniffer_id INT NOT NULL,
    run_at DATETIME NOT NULL,
    started_at DATETIME NOT NULL,
    finished_at DATETIME DEFAULT NULL,
    status VARCHAR(20) NOT NULL,
    source_directory VARCHAR(1024) NOT NULL,
    pages_count INT NOT NULL,
    error_message LONGTEXT DEFAULT NULL,
    INDEX IDX_WEBSITE_SNIFFER_RUN_SNIFFER (sniffer_id),
    INDEX IDX_WEBSITE_SNIFFER_RUN_RUN_AT (run_at),
    INDEX IDX_WEBSITE_SNIFFER_RUN_STATUS (status),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4
SQL);

        $this->addSql(<<<'SQL'
CREATE TABLE website_sniffer_page (
    id INT AUTO_INCREMENT NOT NULL,
    run_id INT NOT NULL,
    relative_path VARCHAR(1024) NOT NULL,
    file_path VARCHAR(1024) NOT NULL,
    title VARCHAR(500) DEFAULT NULL,
    h1 VARCHAR(500) DEFAULT NULL,
    meta_description LONGTEXT DEFAULT NULL,
    body_text_length INT DEFAULT NULL,
    file_size INT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    INDEX IDX_WEBSITE_SNIFFER_PAGE_RUN (run_id),
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8mb4
SQL);

        $this->addSql(<<<'SQL'
ALTER TABLE website_sniffer_run
    ADD CONSTRAINT FK_WEBSITE_SNIFFER_RUN_SNIFFER FOREIGN KEY (sniffer_id) REFERENCES website_sniffer (id) ON DELETE CASCADE
SQL);

        $this->addSql(<<<'SQL'
ALTER TABLE website_sniffer_page
    ADD CONSTRAINT FK_WEBSITE_SNIFFER_PAGE_RUN FOREIGN KEY (run_id) REFERENCES website_sniffer_run (id) ON DELETE CASCADE
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE website_sniffer_page DROP FOREIGN KEY FK_WEBSITE_SNIFFER_PAGE_RUN');
        $this->addSql('ALTER TABLE website_sniffer_run DROP FOREIGN KEY FK_WEBSITE_SNIFFER_RUN_SNIFFER');
        $this->addSql('DROP TABLE website_sniffer_page');
        $this->addSql('DROP TABLE website_sniffer_run');
        $this->addSql('DROP TABLE website_sniffer');
    }
}
