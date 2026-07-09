<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305194636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE zugangsdaten (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, benutzer VARCHAR(255) DEFAULT NULL, passwort LONGTEXT DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, notizen LONGTEXT DEFAULT NULL, active TINYINT NOT NULL, sorting INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE abo DROP FOREIGN KEY `FK_3C920DE9544BFFB1`');
        $this->addSql('ALTER TABLE abo CHANGE zahlweise zahlweise VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE abo ADD CONSTRAINT FK_3C920DE938F090DE FOREIGN KEY (preisstaffel_id) REFERENCES abo_auswahl_preisstaffel (id)');
        $this->addSql('ALTER TABLE abo RENAME INDEX idx_3c920de95d69d08f TO IDX_3C920DE9125D87FE');
        $this->addSql('ALTER TABLE abo RENAME INDEX idx_3c920de9544bffb1 TO IDX_3C920DE938F090DE');
        $this->addSql('ALTER TABLE abo_auswahl CHANGE art art VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE abo_auswahl_software RENAME INDEX idx_9d08efa15d69d08f TO IDX_19F671E9125D87FE');
        $this->addSql('ALTER TABLE abo_auswahl_software RENAME INDEX idx_9d08efa1d7452741 TO IDX_19F671E9D7452741');
        $this->addSql('ALTER TABLE api_token RENAME INDEX uniq_ba9994b77e4af2a0 TO UNIQ_7BA2F5EB7BA2F5EB');
        $this->addSql('ALTER TABLE customer_api_token RENAME INDEX uniq_c4c0b9c6667d7ec4 TO UNIQ_D05F0DF1B3BC57DA');
        $this->addSql('ALTER TABLE customer_api_token RENAME INDEX idx_c4c0b9c69395c3f3 TO IDX_D05F0DF19395C3F3');
        $this->addSql('ALTER TABLE customer_chat_message RENAME INDEX idx_d0d73fe505aec11 TO IDX_F46B3F0F505AEC11');
        $this->addSql('ALTER TABLE customer_chat_message RENAME INDEX idx_d0d73fe9395c3f3 TO IDX_F46B3F0F9D4738BC');
        $this->addSql('ALTER TABLE demo CHANGE eigene_website eigene_website TINYINT NOT NULL');
        $this->addSql('ALTER TABLE demo RENAME INDEX idx_c22cf20d9395c3f3 TO IDX_D642DFA09D4738BC');
        $this->addSql('ALTER TABLE demo_software RENAME INDEX idx_5f6ee07ea0a1e361 TO IDX_757A5091214B61EA');
        $this->addSql('ALTER TABLE demo_software RENAME INDEX idx_5f6ee07ed7452741 TO IDX_757A5091D7452741');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE zugangsdaten');
        $this->addSql('ALTER TABLE abo DROP FOREIGN KEY FK_3C920DE938F090DE');
        $this->addSql('ALTER TABLE abo CHANGE zahlweise zahlweise VARCHAR(20) DEFAULT \'monatlich\' NOT NULL');
        $this->addSql('ALTER TABLE abo ADD CONSTRAINT `FK_3C920DE9544BFFB1` FOREIGN KEY (preisstaffel_id) REFERENCES abo_auswahl_preisstaffel (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE abo RENAME INDEX idx_3c920de9125d87fe TO IDX_3C920DE95D69D08F');
        $this->addSql('ALTER TABLE abo RENAME INDEX idx_3c920de938f090de TO IDX_3C920DE9544BFFB1');
        $this->addSql('ALTER TABLE abo_auswahl CHANGE art art VARCHAR(20) DEFAULT \'aktiv\' NOT NULL');
        $this->addSql('ALTER TABLE abo_auswahl_software RENAME INDEX idx_19f671e9125d87fe TO IDX_9D08EFA15D69D08F');
        $this->addSql('ALTER TABLE abo_auswahl_software RENAME INDEX idx_19f671e9d7452741 TO IDX_9D08EFA1D7452741');
        $this->addSql('ALTER TABLE api_token RENAME INDEX uniq_7ba2f5eb7ba2f5eb TO UNIQ_BA9994B77E4AF2A0');
        $this->addSql('ALTER TABLE customer_api_token RENAME INDEX idx_d05f0df19395c3f3 TO IDX_C4C0B9C69395C3F3');
        $this->addSql('ALTER TABLE customer_api_token RENAME INDEX uniq_d05f0df1b3bc57da TO UNIQ_C4C0B9C6667D7EC4');
        $this->addSql('ALTER TABLE customer_chat_message RENAME INDEX idx_f46b3f0f505aec11 TO IDX_D0D73FE505AEC11');
        $this->addSql('ALTER TABLE customer_chat_message RENAME INDEX idx_f46b3f0f9d4738bc TO IDX_D0D73FE9395C3F3');
        $this->addSql('ALTER TABLE demo CHANGE eigene_website eigene_website TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE demo RENAME INDEX idx_d642dfa09d4738bc TO IDX_C22CF20D9395C3F3');
        $this->addSql('ALTER TABLE demo_software RENAME INDEX idx_757a5091214b61ea TO IDX_5F6EE07EA0A1E361');
        $this->addSql('ALTER TABLE demo_software RENAME INDEX idx_757a5091d7452741 TO IDX_5F6EE07ED7452741');
    }
}
