<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * DSG-Fehlercodes (OBDII/VAG) für die DSG-Seite inkl. Seed (deutsch).
 */
final class Version20260714100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tabelle tfd_gearbox_dtc_code inkl. Seed';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE tfd_gearbox_dtc_code (
                id INT AUTO_INCREMENT NOT NULL,
                obd_code VARCHAR(20) NOT NULL,
                vag_code VARCHAR(20) DEFAULT NULL,
                description VARCHAR(255) NOT NULL,
                sorting INT NOT NULL DEFAULT 0,
                active TINYINT(1) NOT NULL DEFAULT 1,
                INDEX IDX_GEARBOX_DTC_CODE_OBD (obd_code),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $codes = [
            ['P0562', '16946', 'Bordspannung zu niedrig'],
            ['P0701', '17085', 'Mechatronik-Modul defekt'],
            ['P072B', '21159', 'Gang R nicht einlegbar'],
            ['P072C', '21153', 'Gang 1 nicht einlegbar'],
            ['P072D', '21154', 'Gang 2 nicht einlegbar'],
            ['P072E', '21155', 'Gang 3 nicht einlegbar'],
            ['P072F', '21156', 'Gang 4 nicht einlegbar'],
            ['P073A', '21157', 'Gang 5 nicht einlegbar'],
            ['P073B', '21158', 'Gang 6 nicht einlegbar'],
            ['P073C', '21159', 'Gang 7 nicht einlegbar'],
            ['P0746', '17130', 'Druckregelventil 1 Unterbrechung / Kurzschluss nach Masse'],
            ['P0841', '10784', 'Getriebeöl-Drucksensor/-schalter 1'],
            ['P0850', '17234', 'Park-/Neutral-Schalter Eingangssignal'],
            ['P0868', '17252', 'Getriebeöldruck-Adaption am Grenzwert'],
            ['P1604', '18012', 'Internes Steuergerät'],
            ['P1624', '18032', 'MIL-Anforderungssignal aktiv (auch TCM auf Fehler prüfen!)'],
            ['P1701', '18109', 'Getriebesteuergerät gesperrt'],
            ['P1707', '18115', 'Störung im Mechatronik-Modul'],
            ['P174C', '10742', 'Ventil 1 in Getriebeteil 2 fehlerhaft'],
            ['P175D', '10693', 'Kupplung 1 öffnet unbeabsichtigt'],
            ['P175E', '10694', 'Kupplung 1 schließt unbeabsichtigt'],
            ['P176A', '10725', 'Gangsteller 1 nicht regelbar'],
            ['P176B', '10726', 'Gangsteller 2 nicht regelbar'],
            ['P176C', '10727', 'Gangsteller 3 nicht regelbar'],
            ['P176D', '10728', 'Gangsteller 4 nicht regelbar'],
            ['P176E', '10694', 'Kupplung 2 öffnet unbeabsichtigt'],
            ['P176F', '10696', 'Kupplung 2 schließt unbeabsichtigt'],
            ['P1775', '10047', 'Hydraulikdrucksensor 1 (G193) Druckgrenze erreicht'],
            ['P177B', '10806', 'Kupplung 1 Toleranzgrenze erreicht'],
            ['P177C', '10778', 'Kupplung 2 Toleranzgrenze erreicht'],
            ['P179E', '17597', 'Wählhebelsensor fehlerhaft'],
            ['P17BF', '6079', 'Hydraulikpumpe Schutzfunktion'],
            ['P17D4', '10902', 'Ventil 3 in Getriebeteil 1 fehlerhaft'],
            ['P17D5', '8960', 'Ventil 3 in Getriebeteil 2 fehlerhaft'],
            ['P17D6', '18443', 'Kupplung 1 Druck zu hoch'],
            ['P17D7', '18444', 'Kupplung 2 Druck zu hoch'],
            ['P17D8', '8040', 'Drehmomentbegrenzung wegen Kupplungstemperatur'],
            ['P17E0', '17593', 'Sensor für Gangsteller 1 fehlerhaft'],
            ['P1814', '18222', 'Druckregelventil 1 (N215) Unterbrechung / Kurzschluss nach Masse'],
            ['P1815', '18222', 'Druckregelventil 2 (N215) Kurzschluss nach Plus'],
            ['P1818', '18227', 'Druckregelventil 1 (N216) Unterbrechung / Kurzschluss nach Masse'],
            ['P1819', '18228', 'Druckregelventil 2 (N217) Kurzschluss nach Plus'],
            ['P1895', '10812', 'Funktionseinschränkung wegen Druckabfall'],
            ['P1898', '18306', 'Kupplung 1 Funktionseinschränkung'],
            ['P1899', '18307', 'Kupplung 2 Funktionseinschränkung'],
            ['P189A', '10817', 'Kupplung 1 Lüftspiel zu klein'],
            ['P189B', '10818', 'Kupplung 2 Lüftspiel zu klein'],
            ['P189C', '10878', 'Funktionseinschränkung wegen unzureichendem Druckaufbau'],
            ['P2711', '19143', 'Unerwartetes / unplausibles mechanisches Gangauslegen'],
        ];

        foreach ($codes as $index => [$obd, $vag, $description]) {
            $this->addSql(
                'INSERT INTO tfd_gearbox_dtc_code (obd_code, vag_code, description, sorting, active) VALUES (?, ?, ?, ?, 1)',
                [$obd, $vag, $description, ($index + 1) * 10]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tfd_gearbox_dtc_code');
    }
}
