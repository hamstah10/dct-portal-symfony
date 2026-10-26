<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PerformanceFormula;
use App\Entity\VehicleEngine;

final class PerformanceCalculator
{
    public function calculateFormula(PerformanceFormula $formula, VehicleEngine $engine): ?float
    {
        $requiredFields = $formula->getRequiredFields();
        $values = [];

        foreach ($requiredFields as $field) {
            $getter = 'get' . str_replace('_', '', ucwords($field, '_'));

            if (!method_exists($engine, $getter)) {
                return null;
            }

            $value = $engine->$getter();
            if ($value === null) {
                return null;
            }

            $values[$field] = (float) $value;
        }

        return $this->evaluateFormula($formula->getFormula(), $values);
    }

    private function evaluateFormula(string $formula, array $values): ?float
    {
        try {
            $expression = $formula;

            foreach ($values as $field => $value) {
                $expression = str_replace('{' . $field . '}', (string) $value, $expression);
            }

            $expression = preg_replace('/[^0-9+\-*\/%().,\s]/', '', $expression);

            if (preg_match('/[^0-9+\-*\/%().,\s]/', $expression)) {
                return null;
            }

            $result = @eval('return ' . $expression . ';');

            return is_numeric($result) ? (float) $result : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
