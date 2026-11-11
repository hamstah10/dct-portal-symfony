<?php

namespace Tfd\PartnerWizardBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardPackage;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardQuestion;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardQuestionOption;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardStep;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardTool;

/**
 * Liest und ändert alle bearbeitbaren Texte eines Partner-Wizard-Clients
 * (Inline-Editor in der Vorschau, /api/tfd-partner-wizard/admin/texts/{clientId}).
 */
final class PartnerWizardTextService
{
    /**
     * Erlaubte Felder je Ziel: [Getter, Setter, max. Länge (null = text), nullable].
     *
     * @var array<string, array<string, array{0: string, 1: string, 2: int|null, 3: bool}>>
     */
    private const FIELDS = [
        'client' => [
            'headline' => ['getHeadline', 'setHeadline', 180, false],
            'introText' => ['getIntroText', 'setIntroText', null, false],
            'privacyNotice' => ['getPrivacyNotice', 'setPrivacyNotice', null, false],
            'toolDisclaimer' => ['getToolDisclaimer', 'setToolDisclaimer', null, false],
        ],
        'step' => [
            'label' => ['getLabel', 'setLabel', 140, false],
        ],
        'question' => [
            'title' => ['getTitle', 'setTitle', 180, false],
            'helpText' => ['getHelpText', 'setHelpText', null, true],
        ],
        'option' => [
            'title' => ['getTitle', 'setTitle', 140, false],
            'text' => ['getText', 'setText', null, true],
        ],
        'package' => [
            'title' => ['getTitle', 'setTitle', 140, false],
            'description' => ['getDescription', 'setDescription', null, false],
            'ctaLabel' => ['getCtaLabel', 'setCtaLabel', 120, false],
        ],
        'tool' => [
            'name' => ['getName', 'setName', 140, false],
            'description' => ['getDescription', 'setDescription', null, true],
        ],
    ];

    private const ENTITY_CLASSES = [
        'step' => PartnerWizardStep::class,
        'question' => PartnerWizardQuestion::class,
        'option' => PartnerWizardQuestionOption::class,
        'package' => PartnerWizardPackage::class,
        'tool' => PartnerWizardTool::class,
    ];

    private const MAX_TEXT_LENGTH = 5000;
    private const MAX_CHANGES = 200;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PartnerWizardTextRegistry $registry,
    ) {
    }

    public function listTexts(PartnerWizardClient $client): array
    {
        $custom = $client->getCustomLabels();
        $labels = [];
        foreach ($this->registry->all() as $key => $definition) {
            $override = $custom[$key] ?? null;
            $labels[] = [
                'key' => $key,
                'group' => $definition['group'],
                'label' => $definition['label'],
                'default' => $definition['default'],
                'multiline' => (bool) ($definition['multiline'] ?? false),
                'value' => is_string($override) ? $override : $definition['default'],
                'overridden' => is_string($override),
            ];
        }

        $clientFields = [];
        foreach (self::FIELDS['client'] as $field => [$getter]) {
            $clientFields[$field] = $client->{$getter}();
        }

        $steps = $this->em->getRepository(PartnerWizardStep::class)->findBy(['client' => $client], ['priority' => 'ASC']);

        return [
            'clientId' => $client->getClientId(),
            'labels' => $labels,
            'client' => $clientFields,
            'steps' => array_map(fn (PartnerWizardStep $step) => [
                'id' => $step->getId(),
                'label' => $step->getLabel(),
                'questions' => array_map(fn (PartnerWizardQuestion $question) => [
                    'id' => $question->getId(),
                    'title' => $question->getTitle(),
                    'helpText' => $question->getHelpText(),
                    'options' => array_map(fn (PartnerWizardQuestionOption $option) => [
                        'id' => $option->getId(),
                        'title' => $option->getTitle(),
                        'text' => $option->getText(),
                    ], $question->getOptions()->toArray()),
                ], $step->getQuestions()->toArray()),
            ], $steps),
            'packages' => array_map(fn (PartnerWizardPackage $package) => [
                'id' => $package->getId(),
                'key' => $package->getPackageKey(),
                'title' => $package->getTitle(),
                'description' => $package->getDescription(),
                'ctaLabel' => $package->getCtaLabel(),
            ], $this->em->getRepository(PartnerWizardPackage::class)->findBy(['client' => $client], ['priority' => 'ASC'])),
            'tools' => array_map(fn (PartnerWizardTool $tool) => [
                'id' => $tool->getId(),
                'key' => $tool->getToolKey(),
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
            ], $this->em->getRepository(PartnerWizardTool::class)->findBy(['client' => $client], ['priority' => 'ASC'])),
        ];
    }

    /**
     * Prüft alle Änderungen; nur wenn keine fehlerhaft ist, wird gespeichert (alles oder nichts).
     *
     * @param mixed $changes
     * @return array{applied: int, errors: list<array{index: int, error: string}>}
     */
    public function applyChanges(PartnerWizardClient $client, mixed $changes): array
    {
        if (!is_array($changes) || !array_is_list($changes) || $changes === []) {
            return ['applied' => 0, 'errors' => [['index' => -1, 'error' => '"changes" muss eine nicht-leere Liste sein.']]];
        }
        if (count($changes) > self::MAX_CHANGES) {
            return ['applied' => 0, 'errors' => [['index' => -1, 'error' => 'Zu viele Änderungen in einem Request.']]];
        }

        $errors = [];
        /** @var list<callable(): void> $operations */
        $operations = [];
        $labels = $client->getCustomLabels();

        foreach ($changes as $index => $change) {
            if (!is_array($change)) {
                $errors[] = ['index' => $index, 'error' => 'Ungültiger Eintrag.'];
                continue;
            }

            $target = (string) ($change['target'] ?? '');
            $value = $change['value'] ?? null;
            if ($value !== null && !is_string($value)) {
                $errors[] = ['index' => $index, 'error' => '"value" muss Text oder null sein.'];
                continue;
            }
            $value = $value === null ? null : $this->sanitize($value);

            if ($target === 'label') {
                $key = (string) ($change['key'] ?? '');
                if (!$this->registry->has($key)) {
                    $errors[] = ['index' => $index, 'error' => sprintf('Unbekannter Text-Schlüssel "%s".', $key)];
                    continue;
                }
                if ($value !== null && mb_strlen($value) > self::MAX_TEXT_LENGTH) {
                    $errors[] = ['index' => $index, 'error' => 'Text ist zu lang.'];
                    continue;
                }
                if ($value === null || $value === $this->registry->getDefault($key)) {
                    unset($labels[$key]);
                } else {
                    $labels[$key] = $value;
                }
                continue;
            }

            if (!isset(self::FIELDS[$target])) {
                $errors[] = ['index' => $index, 'error' => sprintf('Unbekanntes Ziel "%s".', $target)];
                continue;
            }

            $field = (string) ($change['field'] ?? '');
            if (!isset(self::FIELDS[$target][$field])) {
                $errors[] = ['index' => $index, 'error' => sprintf('Feld "%s" ist für "%s" nicht bearbeitbar.', $field, $target)];
                continue;
            }
            [, $setter, $maxLength, $nullable] = self::FIELDS[$target][$field];

            if ($value === null || $value === '') {
                if (!$nullable) {
                    $errors[] = ['index' => $index, 'error' => 'Dieses Feld darf nicht leer sein.'];
                    continue;
                }
                $value = null;
            }
            if ($value !== null && mb_strlen($value) > ($maxLength ?? self::MAX_TEXT_LENGTH)) {
                $errors[] = ['index' => $index, 'error' => sprintf('Maximal %d Zeichen erlaubt.', $maxLength ?? self::MAX_TEXT_LENGTH)];
                continue;
            }

            if ($target === 'client') {
                $entity = $client;
            } else {
                $entity = $this->findOwnedEntity($client, $target, $change['id'] ?? null);
                if ($entity === null) {
                    $errors[] = ['index' => $index, 'error' => sprintf('%s nicht gefunden.', ucfirst($target))];
                    continue;
                }
            }

            $operations[] = static function () use ($entity, $setter, $value): void {
                $entity->{$setter}($value);
            };
        }

        if ($errors !== []) {
            return ['applied' => 0, 'errors' => $errors];
        }

        foreach ($operations as $operation) {
            $operation();
        }
        $client->setCustomLabels($labels);
        $this->em->flush();

        return ['applied' => count($changes), 'errors' => []];
    }

    private function findOwnedEntity(PartnerWizardClient $client, string $target, mixed $id): ?object
    {
        if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
            return null;
        }

        $entity = $this->em->getRepository(self::ENTITY_CLASSES[$target])->find((int) $id);
        if ($entity === null) {
            return null;
        }

        $owner = match (true) {
            $entity instanceof PartnerWizardStep, $entity instanceof PartnerWizardPackage, $entity instanceof PartnerWizardTool => $entity->getClient(),
            $entity instanceof PartnerWizardQuestion => $entity->getStep()?->getClient(),
            $entity instanceof PartnerWizardQuestionOption => $entity->getQuestion()?->getStep()?->getClient(),
            default => null,
        };

        return $owner === $client ? $entity : null;
    }

    private function sanitize(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = strip_tags($value);

        return trim($value);
    }
}
