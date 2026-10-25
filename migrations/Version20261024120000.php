<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261024120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create knowledge_entry table and seed the KI-Wissen questions from the planning';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE knowledge_entry (
                id INT AUTO_INCREMENT NOT NULL,
                code VARCHAR(16) NOT NULL,
                group_title VARCHAR(120) NOT NULL,
                status VARCHAR(16) NOT NULL,
                question LONGTEXT NOT NULL,
                answer_label VARCHAR(120) DEFAULT NULL,
                answer JSON DEFAULT NULL,
                list_items JSON DEFAULT NULL,
                note LONGTEXT DEFAULT NULL,
                position INT NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE INDEX UNIQ_KNOWLEDGE_ENTRY_CODE (code),
                INDEX IDX_KNOWLEDGE_ENTRY_POSITION (position),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);

        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $json = static fn (?array $value): ?string => null === $value ? null : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $rows = [
            ['F1', 'Grundsatzfragen', 'answered', 'Welches Wissen soll die eigene KI haben?', 'Antwort des Nutzers', ['Fahrzeugdaten (Dyno, Olsx, Datenbank), eigene Dokumente sowie Formeln und Berechnungen für die Fahrzeugdatenbank.'], null, 'Webseiten und Support-Tickets wurden nicht gewählt.'],
            ['F2', 'Grundsatzfragen', 'answered', 'Wo soll das Wissen abgefragt werden?', 'Antwort des Nutzers', ['Im Marken-Assistenten im Superadmin. Der Nutzer hat diese Empfehlung bestätigt.'], null, null],
            ['F3', 'Grundsatzfragen', 'answered', 'Plan-Freigabe', 'Antwort des Nutzers', ['Der Plan ist genehmigt. Umgesetzt werden die Schritte 2 und 3.'], null, null],
            ['F4', 'Technik und Werkzeuge', 'answered', 'Braucht die Lösung eine RAG-Datenbank?', 'Entscheidung', ['Ja, aber nur für Dokumente. Fahrzeugdaten werden über Abfrage-Werkzeuge gelesen, Formeln über ein Rechenwerkzeug. Das Sprachmodell rechnet nicht selbst.'], null, null],
            ['F5', 'Technik und Werkzeuge', 'answered', 'Welche Websuche nutzt der Assistent?', 'Entscheidung', ['Perplexity. Der Nutzer hat diesen Anbieter ausdrücklich gewählt, nicht SerpAPI, Firecrawl oder Brave.'], null, null],
            ['F6', 'Technik und Werkzeuge', 'answered', 'Welches Modell führt den Marken-Assistenten?', 'Entscheidung', ['Claude von Anthropic, derzeit claude-sonnet-4-5-20250929. Das installierte Paket kennt nur Modelle bis 4.5.'], null, null],
            ['O1', 'Offene Fragen', 'open', 'Welche Formeln sollen hinterlegt werden, mit Einheiten, und wer pflegt sie?', null, null, null, 'Das bestimmt das Datenmodell der Entität AiFormula.'],
            ['O2', 'Offene Fragen', 'open', 'Welche Dokumente kommen zuerst in die Wissensbasis?', null, null, null, null],
            ['O3', 'Offene Fragen', 'open', 'Ab wann gilt die Leistung einer Stage als unplausibel?', 'Vorschlag des Nutzers', ['Leistung oder Drehmoment einer Stage gegen das Original prüfen. Stage über dem Original um mehr als X % markieren.'], null, 'Der Schwellenwert X fehlt noch.'],
            ['O4', 'Offene Fragen', 'open', 'Wie wird aus der Dyno-Kurve von Stage 1 die Kurve für Stage 2 abgeleitet?', 'Vorschlag des Nutzers', ['Eine geschätzte Kurve für Stage 2 berechnen, damit auch dort ein Dyno-Diagramm entsteht.'], null, 'Offen ist die Ableitungsregel: Faktor, Gewinn-Differenz oder Kurvenverschiebung.'],
            ['D1', 'Datenlage', 'checked', 'Gibt es Kurven, aus denen sich Stage 2 ableiten lässt?', null, null, ['11 910 Datenbank-Stages haben eine dyno_chart mit dem Original und den Stage-Kurven als Punkte aus U/min und Wert.', 'Die Dyno-Dateien enthalten nur Endwerte je Stage (power_ps, torque_nm), keine Kurven.'], null],
        ];

        foreach ($rows as $position => [$code, $group, $status, $question, $answerLabel, $answer, $listItems, $note]) {
            $this->addSql(
                'INSERT INTO knowledge_entry (code, group_title, status, question, answer_label, answer, list_items, note, position, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$code, $group, $status, $question, $answerLabel, $json($answer), $json($listItems), $note, $position + 1, $now],
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE knowledge_entry');
    }
}
