<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260704050000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add shop product link to tfd_partner_wizard_tool';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool ADD shop_product_uid INT DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_TFD_PW_TOOL_SHOP_PRODUCT ON tfd_partner_wizard_tool (shop_product_uid)');
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool ADD CONSTRAINT FK_TFD_PW_TOOL_SHOP_PRODUCT FOREIGN KEY (shop_product_uid) REFERENCES tx_tuningtoolshop_domain_model_product (uid) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool DROP FOREIGN KEY FK_TFD_PW_TOOL_SHOP_PRODUCT');
        $this->addSql('DROP INDEX IDX_TFD_PW_TOOL_SHOP_PRODUCT ON tfd_partner_wizard_tool');
        $this->addSql('ALTER TABLE tfd_partner_wizard_tool DROP shop_product_uid');
    }
}
