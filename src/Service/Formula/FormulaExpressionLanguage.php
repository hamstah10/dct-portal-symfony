<?php

declare(strict_types=1);

namespace App\Service\Formula;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Ausdruckssprache für Formeln: nur freigegebene Rechenfunktionen, kein constant() o. Ä.
 */
final class FormulaExpressionLanguage extends ExpressionLanguage
{
    protected function registerFunctions(): void
    {
        $functions = [
            'round' => static fn (float|int $value, int $precision = 0): float => round((float) $value, $precision),
            'floor' => static fn (float|int $value): float => floor((float) $value),
            'ceil' => static fn (float|int $value): float => ceil((float) $value),
            'abs' => static fn (float|int $value): float|int => abs($value),
            'min' => static fn (float|int ...$values): float|int => min($values),
            'max' => static fn (float|int ...$values): float|int => max($values),
            'clamp' => static fn (float|int $value, float|int $low, float|int $high): float|int => max($low, min($high, $value)),
        ];

        foreach ($functions as $name => $callback) {
            $this->addFunction(new ExpressionFunction(
                $name,
                static fn (): string => throw new \LogicException('Formeln werden nur ausgewertet, nicht kompiliert.'),
                static fn (array $arguments, mixed ...$args): mixed => $callback(...$args),
            ));
        }
    }
}
