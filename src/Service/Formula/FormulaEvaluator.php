<?php

declare(strict_types=1);

namespace App\Service\Formula;

use App\Entity\Formula;
use App\Repository\FormulaRepository;
use Symfony\Component\ExpressionLanguage\SyntaxError;

/**
 * Wertet Formeln aus dem Bereich „Formeln & Logik“ aus. Andere Bereiche rufen evaluate() mit dem Schlüssel auf.
 */
final class FormulaEvaluator
{
    private readonly FormulaExpressionLanguage $language;

    public function __construct(private readonly FormulaRepository $formulas)
    {
        $this->language = new FormulaExpressionLanguage();
    }

    /**
     * @param array<string, float|int|string|bool|null> $variables
     *
     * @return array{result: float|int|string|bool|null, rule: ?string, error: ?string}
     */
    public function evaluateByKey(string $key, array $variables): array
    {
        $formula = $this->formulas->findOneByKey($key);
        if (null === $formula || !$formula->isActive()) {
            return ['result' => null, 'rule' => null, 'error' => sprintf('Formel "%s" nicht gefunden oder inaktiv.', $key)];
        }

        return $this->evaluate($formula, $variables);
    }

    /**
     * Fehlende Variablen werden mit dem hinterlegten Standardwert belegt.
     *
     * @param array<string, float|int|string|bool|null> $variables
     *
     * @return array{result: float|int|string|bool|null, rule: ?string, error: ?string}
     */
    public function evaluate(Formula $formula, array $variables): array
    {
        return $this->run($formula->getRules(), $formula->getVariables(), $variables);
    }

    /**
     * Prüft Syntax und Namen aller Regeln, ohne zu rechnen.
     *
     * @param list<array{label?: string, condition?: string, expression?: string}> $rules
     * @param list<array{name: string, label?: string, default?: mixed}>           $variableDefinitions
     *
     * @return list<string> Fehlermeldungen, leer wenn alles gültig ist
     */
    public function validate(array $rules, array $variableDefinitions): array
    {
        $names = array_column($variableDefinitions, 'name');
        $errors = [];

        foreach ($rules as $index => $rule) {
            foreach (['condition' => 'Bedingung', 'expression' => 'Formel'] as $field => $title) {
                $source = trim((string) ($rule[$field] ?? ''));
                if ('' === $source) {
                    if ('expression' === $field) {
                        $errors[] = sprintf('Regel %d: Formel fehlt.', $index + 1);
                    }
                    continue;
                }

                try {
                    $this->language->lint($source, $names);
                } catch (SyntaxError $e) {
                    $errors[] = sprintf('Regel %d, %s: %s', $index + 1, $title, $e->getMessage());
                }
            }
        }

        return $errors;
    }

    /**
     * @param list<array{label?: string, condition?: string, expression?: string}> $rules
     * @param list<array{name: string, label?: string, default?: mixed}>           $variableDefinitions
     * @param array<string, mixed>                                                 $input
     *
     * @return array{result: float|int|string|bool|null, rule: ?string, error: ?string}
     */
    public function run(array $rules, array $variableDefinitions, array $input): array
    {
        $values = [];
        foreach ($variableDefinitions as $definition) {
            $name = (string) $definition['name'];
            $value = $input[$name] ?? $definition['default'] ?? null;
            $values[$name] = is_numeric($value) ? $value + 0 : $value;
        }

        try {
            foreach ($rules as $index => $rule) {
                $condition = trim((string) ($rule['condition'] ?? ''));
                if ('' !== $condition && !$this->language->evaluate($condition, $values)) {
                    continue;
                }

                $label = trim((string) ($rule['label'] ?? ''));

                return [
                    'result' => $this->language->evaluate((string) ($rule['expression'] ?? ''), $values),
                    'rule' => '' !== $label ? $label : sprintf('Regel %d', $index + 1),
                    'error' => null,
                ];
            }
        } catch (\Throwable $e) {
            return ['result' => null, 'rule' => null, 'error' => $e->getMessage()];
        }

        return ['result' => null, 'rule' => null, 'error' => 'Keine Regel trifft zu. Eine Regel ohne Bedingung als Standardfall ergänzen.'];
    }
}
