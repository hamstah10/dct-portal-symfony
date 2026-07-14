<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260709043205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add tfd_partner_wizard_client_shopproduct join table for client-level product recommendations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tfd_partner_wizard_client_shopproduct (client_id INT NOT NULL, shop_product_uid INT NOT NULL, INDEX IDX_2D03FD3319EB6921 (client_id), INDEX IDX_2D03FD3361C3A409 (shop_product_uid), PRIMARY KEY (client_id, shop_product_uid)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_shopproduct ADD CONSTRAINT FK_2D03FD3319EB6921 FOREIGN KEY (client_id) REFERENCES tfd_partner_wizard_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_shopproduct ADD CONSTRAINT FK_2D03FD3361C3A409 FOREIGN KEY (shop_product_uid) REFERENCES tx_tuningtoolshop_domain_model_product (uid) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_shopproduct DROP FOREIGN KEY FK_2D03FD3319EB6921');
        $this->addSql('ALTER TABLE tfd_partner_wizard_client_shopproduct DROP FOREIGN KEY FK_2D03FD3361C3A409');
        $this->addSql('DROP TABLE tfd_partner_wizard_client_shopproduct');
    }
}
