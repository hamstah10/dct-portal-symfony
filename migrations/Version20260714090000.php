<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Stage-Infos ("Stages im Überblick") für die DSG-Seite inkl. Feature-Listen + Seed.
 */
final class Version20260714090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Tabellen tfd_gearbox_stage_info + tfd_gearbox_stage_info_feature inkl. Seed';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE tfd_gearbox_stage_info (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(150) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                sorting INT NOT NULL DEFAULT 0,
                active TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE tfd_gearbox_stage_info_feature (
                id INT AUTO_INCREMENT NOT NULL,
                stage_info_id INT NOT NULL,
                label VARCHAR(255) NOT NULL,
                included TINYINT(1) NOT NULL DEFAULT 0,
                sorting INT NOT NULL DEFAULT 0,
                INDEX IDX_GEARBOX_STAGE_INFO_FEATURE_INFO (stage_info_id),
                PRIMARY KEY (id),
                CONSTRAINT FK_GEARBOX_STAGE_INFO_FEATURE_INFO FOREIGN KEY (stage_info_id)
                    REFERENCES tfd_gearbox_stage_info (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $infos = [
            [
                'name' => 'Fahrbarkeit',
                'description' => 'Die Fahrbarkeits- & Lebensdauer-Software ist ein empfehlenswertes Upgrade für nahezu alle VAG-DSG-Getriebe. Die Schaltstrategien ab Werk sind heute vor allem auf niedrige Emissionen und geringen Verbrauch ausgelegt – oft zulasten von Fahrbarkeit, Haltbarkeit und Zuverlässigkeit des Getriebes. Dazu kommen zahlreiche bekannte DSG-Fahrbarkeits- und Haltbarkeitsprobleme ab Werk, die sich per Software beheben lassen!',
                'audience' => 'Serienfahrzeuge (ohne Tuning)',
                'flags' => [true, true, true, false, false, false, false],
            ],
            [
                'name' => 'Stage 1',
                'description' => 'Die DSG-Stage-1-Software ist die günstige Einstiegslösung: Es werden ausschließlich die notwendigen Drehmomentbegrenzer angehoben – vor allem geeignet für sehr mild getunte Fahrzeuge. Für die meisten getunten Fahrzeuge sind zusätzliche Software-Anpassungen nötig, um ausreichende Kupplungsdrücke zu erreichen und das volle Potenzial des Fahrzeugs freizuschalten!',
                'audience' => 'Mild getunte Fahrzeuge',
                'flags' => [false, false, false, false, false, false, false],
            ],
            [
                'name' => 'Stage 2',
                'description' => 'Die DSG-Stage-2-Software ist der erste echte Schritt zu mehr Getriebeperformance! Sie umfasst diverse notwendige Software-Anpassungen für getunte Fahrzeuge und verbessert die Performance beim Anfahren, Beschleunigen sowie beim Hoch- und Runterschalten. Der Fokus liegt klar auf Performance – Fahrbarkeits- und Lebensdauer-Features sind NICHT enthalten!',
                'audience' => 'Mild und stark getunte Fahrzeuge',
                'flags' => [true, false, false, true, false, false, false],
            ],
            [
                'name' => 'Stage 2+',
                'description' => 'Die DSG-Stage-2+-Software ist das Preis-Leistungs-Paket für dein DSG-Getriebe! Sie enthält alle Fahrbarkeits- & Lebensdauer-Features, die notwendigen Performance-Anpassungen für getunte Fahrzeuge und zusätzliche Optionen gleich mit!',
                'audience' => 'Mild und stark getunte Fahrzeuge',
                'flags' => [true, true, true, true, false, false, true],
            ],
            [
                'name' => 'Stage 3',
                'description' => 'Die DSG-Stage-3-Software zählt zur stärksten DSG-Software am Markt und ist speziell für High-Performance-Anwendungen geeignet. Durch einzigartige Software-Anpassungen werden extrem hohe Kupplungsdrücke erreicht – mit beispiellos hoher Drehmomentkapazität der Serienkupplung! Fahrbarkeit, Haltbarkeit und Zuverlässigkeit bleiben dabei voll erhalten.',
                'audience' => 'Stark getunte Fahrzeuge',
                'flags' => [true, true, true, true, true, false, true],
            ],
            [
                'name' => 'Stage 4',
                'description' => 'DSG-Stage-4 sind maßgeschneiderte Software-Lösungen für DSG-(Race-)Hardware wie Upgrade-Race-Kupplungen und Getriebeumbauten. Diese Stage enthält alle Stage-3-Features plus individuelle Extras wie Race-Kupplungskalibrierungen, angepasste Radsatz-Kalibrierungen, einzigartige Racing-Features und Umbau-Lösungen. Für perfekt aufeinander abgestimmte Hard- und Software empfehlen wir den Einsatz passender DSG-Performance-Hardware!',
                'audience' => 'Stark getunte (Race-)Anwendungen',
                'flags' => [true, true, true, true, true, true, true],
            ],
        ];

        // Feature-Reihenfolge nach der Zielgruppen-Zeile:
        $flagLabels = [
            'Motor- & Getriebediagnose',
            'Verbesserte Fahrbarkeit',
            'Verlängerte Getriebelebensdauer',
            'Gesteigerte Getriebeperformance',
            'Einzigartige Performance-Optionen',
            'Spezielle Race-Kupplungskalibrierungen',
            'Zusätzliche Optionen',
        ];

        foreach ($infos as $index => $info) {
            $this->addSql(
                'INSERT INTO tfd_gearbox_stage_info (name, description, sorting, active) VALUES (?, ?, ?, 1)',
                [$info['name'], $info['description'], ($index + 1) * 10]
            );

            $features = array_merge(
                [[$info['audience'], true]],
                array_map(
                    static fn (string $label, bool $included): array => [$label, $included],
                    $flagLabels,
                    $info['flags']
                )
            );

            foreach ($features as $featureIndex => [$label, $included]) {
                $this->addSql(
                    'INSERT INTO tfd_gearbox_stage_info_feature (stage_info_id, label, included, sorting)
                     SELECT id, ?, ?, ? FROM tfd_gearbox_stage_info WHERE name = ? ORDER BY id DESC LIMIT 1',
                    [$label, $included ? 1 : 0, ($featureIndex + 1) * 10, $info['name']]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE tfd_gearbox_stage_info_feature');
        $this->addSql('DROP TABLE tfd_gearbox_stage_info');
    }
}
