<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260306121000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change ToolMethode to ToolManufacturer relation from many-to-one to many-to-many.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tx_chiptuningdatabase_toolmethode_toolmanufacturer_mm (uid_local INT NOT NULL, uid_foreign INT NOT NULL, INDEX IDX_646D9E955C4C8485 (uid_local), INDEX IDX_646D9E951D17607B (uid_foreign), PRIMARY KEY(uid_local, uid_foreign)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_toolmethode_toolmanufacturer_mm ADD CONSTRAINT FK_646D9E955C4C8485 FOREIGN KEY (uid_local) REFERENCES tx_chiptuningdatabase_domain_model_toolmethode (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_toolmethode_toolmanufacturer_mm ADD CONSTRAINT FK_646D9E951D17607B FOREIGN KEY (uid_foreign) REFERENCES tx_chiptuningdatabase_domain_model_toolmanufacturer (uid)');
        $this->addSql('INSERT INTO tx_chiptuningdatabase_toolmethode_toolmanufacturer_mm (uid_local, uid_foreign) SELECT uid, tool_manufacturer FROM tx_chiptuningdatabase_domain_model_toolmethode WHERE tool_manufacturer IS NOT NULL');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_toolmethode DROP FOREIGN KEY FK_E3D33709B143A831');
        $this->addSql('DROP INDEX IDX_E3D33709B143A831 ON tx_chiptuningdatabase_domain_model_toolmethode');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_toolmethode DROP tool_manufacturer');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_toolmethode ADD tool_manufacturer INT DEFAULT NULL');
        $this->addSql('UPDATE tx_chiptuningdatabase_domain_model_toolmethode tm LEFT JOIN (SELECT uid_local, MIN(uid_foreign) AS uid_foreign FROM tx_chiptuningdatabase_toolmethode_toolmanufacturer_mm GROUP BY uid_local) tmm ON tmm.uid_local = tm.uid SET tm.tool_manufacturer = tmm.uid_foreign');
        $this->addSql('CREATE INDEX IDX_E3D33709B143A831 ON tx_chiptuningdatabase_domain_model_toolmethode (tool_manufacturer)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_domain_model_toolmethode ADD CONSTRAINT FK_E3D33709B143A831 FOREIGN KEY (tool_manufacturer) REFERENCES tx_chiptuningdatabase_domain_model_toolmanufacturer (uid)');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_toolmethode_toolmanufacturer_mm DROP FOREIGN KEY FK_646D9E955C4C8485');
        $this->addSql('ALTER TABLE tx_chiptuningdatabase_toolmethode_toolmanufacturer_mm DROP FOREIGN KEY FK_646D9E951D17607B');
        $this->addSql('DROP TABLE tx_chiptuningdatabase_toolmethode_toolmanufacturer_mm');
    }
}
