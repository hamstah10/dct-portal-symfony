<?php

namespace Tfd\PartnerWizardBundle\Service;

/**
 * Feste UI-Texte des Partner-Wizards (Schlüssel + Standardtext).
 *
 * Quelle ist Resources/config/labels.de.json – dieselbe Datei importiert auch das
 * React-Widget (partner-wizard-widget/src/labels.ts), damit die Schlüssel nie auseinanderlaufen.
 * Überschreibungen pro Client liegen in PartnerWizardClient::$customLabels (key => text).
 */
final class PartnerWizardTextRegistry
{
    /** @var array<string, array{group: string, label: string, default: string, multiline?: bool}>|null */
    private ?array $definitions = null;

    /**
     * @return array<string, array{group: string, label: string, default: string, multiline?: bool}>
     */
    public function all(): array
    {
        if ($this->definitions === null) {
            $raw = file_get_contents(__DIR__ . '/../Resources/config/labels.de.json');
            $data = $raw !== false ? json_decode($raw, true) : null;
            $this->definitions = is_array($data) ? $data : [];
        }

        return $this->definitions;
    }

    public function has(string $key): bool
    {
        return isset($this->all()[$key]);
    }

    public function getDefault(string $key): ?string
    {
        return $this->all()[$key]['default'] ?? null;
    }

    /**
     * Standardtexte + gültige Überschreibungen des Clients.
     *
     * @param array<mixed> $customLabels
     * @return array<string, string>
     */
    public function resolve(array $customLabels): array
    {
        $labels = [];
        foreach ($this->all() as $key => $definition) {
            $override = $customLabels[$key] ?? null;
            $labels[$key] = is_string($override) ? $override : $definition['default'];
        }

        return $labels;
    }
}
