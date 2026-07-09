<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260308113000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add credit categories and packages.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE credit_category (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE credit_package (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, title VARCHAR(150) NOT NULL, credits INT NOT NULL, price NUMERIC(10, 2) NOT NULL, price_per_file NUMERIC(10, 2) NOT NULL, INDEX IDX_CREDIT_PACKAGE_CATEGORY (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE credit_package ADD CONSTRAINT FK_CREDIT_PACKAGE_CATEGORY FOREIGN KEY (category_id) REFERENCES credit_category (id) ON DELETE CASCADE');

        $this->addSql("INSERT INTO credit_category (title) VALUES ('Tuning Credits'), ('EVC Credits')");
        $this->addSql(
            "INSERT INTO credit_package (category_id, title, credits, price, price_per_file) VALUES
            (1, '10 Credits', 10, 12.00, 0.00),
            (1, '50 credits', 50, 58.00, 0.00),
            (1, '100 Credits', 100, 115.00, 115.00),
            (1, '500 Credits', 500, 550.00, 110.00),
            (1, '1000 Credits', 1000, 1050.00, 105.00),
            (1, '2000 Credits', 2000, 2000.00, 100.00),
            (1, '3000 Credits', 3000, 2850.00, 95.00),
            (1, '4000 Credits', 4000, 3600.00, 90.00),
            (1, '5000 Credits', 5000, 4250.00, 85.00),
            (2, '10 OLSx-Credits', 10, 60.00, 60.00)"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE credit_package DROP FOREIGN KEY FK_CREDIT_PACKAGE_CATEGORY');
        $this->addSql('DROP TABLE credit_package');
        $this->addSql('DROP TABLE credit_category');
    }
}

