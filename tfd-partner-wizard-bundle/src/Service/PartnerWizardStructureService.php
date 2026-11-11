<?php

namespace Tfd\PartnerWizardBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardQuestion;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardQuestionOption;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardStep;

/**
 * Fragen und Antwortoptionen eines Clients über die Headless-API anlegen, ändern und löschen.
 *
 * Alle Methoden geben bei Fehlern ['errors' => [...]] zurück und ändern dann nichts.
 */
final class PartnerWizardStructureService
{
    public const QUESTION_TYPES = ['generic', 'vehicleType', 'toolManufacturer', 'volume'];
    public const FIELD_TYPES = ['text', 'textarea', 'email', 'select', 'checkbox', 'radio'];
    /** Fragetypen, deren Optionen aus der Client-Konfiguration kommen (nicht aus eigenen Optionen). */
    private const EXTERNAL_OPTION_TYPES = ['vehicleType', 'toolManufacturer'];
    private const KEY_PATTERN = '/^[A-Za-z][A-Za-z0-9_]*$/';
    private const MAX_TEXT_LENGTH = 5000;
    private const MAX_OPTIONS = 100;

    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function listStructure(PartnerWizardClient $client): array
    {
        $steps = $this->em->getRepository(PartnerWizardStep::class)->findBy(['client' => $client], ['priority' => 'ASC', 'id' => 'ASC']);

        return [
            'clientId' => $client->getClientId(),
            'questionTypes' => self::QUESTION_TYPES,
            'fieldTypes' => self::FIELD_TYPES,
            'steps' => array_map(fn (PartnerWizardStep $step) => [
                'id' => $step->getId(),
                'key' => $step->getStepKey(),
                'label' => $step->getLabel(),
                'type' => $step->getType(),
                'priority' => $step->getPriority(),
                'active' => $step->isActive(),
                'questions' => array_map(fn (PartnerWizardQuestion $question) => $this->serializeQuestion($question), $step->getQuestions()->toArray()),
            ], $steps),
        ];
    }

    public function serializeQuestion(PartnerWizardQuestion $question): array
    {
        return [
            'id' => $question->getId(),
            'stepId' => $question->getStep()?->getId(),
            'answerKey' => $question->getAnswerKey(),
            'title' => $question->getTitle(),
            'helpText' => $question->getHelpText(),
            'questionType' => $question->getQuestionType(),
            'multiSelect' => $question->isMultiSelect(),
            'fieldType' => $question->getFieldType(),
            'required' => $question->isRequired(),
            'dependsOnAnswerKey' => $question->getDependsOnAnswerKey(),
            'dependsOnValue' => $question->getDependsOnValue(),
            'priority' => $question->getPriority(),
            'active' => $question->isActive(),
            'optionsFromClientConfig' => in_array($question->getQuestionType(), self::EXTERNAL_OPTION_TYPES, true),
            'options' => array_map(fn (PartnerWizardQuestionOption $option) => $this->serializeOption($option), $question->getOptions()->toArray()),
        ];
    }

    public function serializeOption(PartnerWizardQuestionOption $option): array
    {
        return [
            'id' => $option->getId(),
            'questionId' => $option->getQuestion()?->getId(),
            'value' => $option->getValue(),
            'title' => $option->getTitle(),
            'text' => $option->getText(),
            'numericValue' => $option->getNumericValue(),
            'priority' => $option->getPriority(),
            'active' => $option->isActive(),
        ];
    }

    // ------------------------------------------------------------------ Fragen

    /** @return array{question?: PartnerWizardQuestion, errors: list<string>} */
    public function createQuestion(PartnerWizardClient $client, array $data): array
    {
        $errors = [];
        foreach (['stepId', 'answerKey', 'title'] as $required) {
            if (!array_key_exists($required, $data)) {
                $errors[] = sprintf('"%s" fehlt.', $required);
            }
        }
        $optionsData = $data['options'] ?? [];
        if (!is_array($optionsData) || !array_is_list($optionsData)) {
            $errors[] = '"options" muss eine Liste sein.';
            $optionsData = [];
        } elseif (count($optionsData) > self::MAX_OPTIONS) {
            $errors[] = sprintf('Maximal %d Optionen.', self::MAX_OPTIONS);
        }
        if ($errors !== []) {
            return ['errors' => $errors];
        }

        $question = new PartnerWizardQuestion();
        $errors = $this->applyQuestionData($client, $question, $data, true);

        if ($optionsData !== [] && in_array($question->getQuestionType(), self::EXTERNAL_OPTION_TYPES, true)) {
            $errors[] = sprintf('Fragetyp "%s" nutzt die Auswahl aus der Client-Konfiguration – eigene Optionen sind nicht möglich.', $question->getQuestionType());
        }

        $values = [];
        foreach ($optionsData as $index => $optionData) {
            if (!is_array($optionData)) {
                $errors[] = sprintf('options[%d]: ungültiger Eintrag.', $index);
                continue;
            }
            $option = new PartnerWizardQuestionOption();
            foreach ($this->applyOptionData($option, $optionData, true, null) as $error) {
                $errors[] = sprintf('options[%d]: %s', $index, $error);
            }
            if (isset($values[$option->getValue()])) {
                $errors[] = sprintf('options[%d]: Wert "%s" doppelt.', $index, $option->getValue());
            }
            $values[$option->getValue()] = true;
            if (!array_key_exists('priority', $optionData)) {
                $option->setPriority(($index + 1) * 10);
            }
            $option->setQuestion($question);
            $question->getOptions()->add($option);
        }

        if ($errors !== []) {
            return ['errors' => $errors];
        }

        $this->em->persist($question);
        $this->em->flush();

        return ['question' => $question, 'errors' => []];
    }

    /** @return array{question?: PartnerWizardQuestion, errors: list<string>} */
    public function updateQuestion(PartnerWizardQuestion $question, array $data): array
    {
        if (array_key_exists('options', $data)) {
            return ['errors' => ['Optionen werden einzeln über /options bearbeitet.']];
        }

        $client = $question->getStep()?->getClient();
        $oldAnswerKey = $question->getAnswerKey();
        $errors = $this->applyQuestionData($client, $question, $data, false);

        if ($errors === [] && $oldAnswerKey !== $question->getAnswerKey() && ($dependents = $this->dependentQuestions($client, $oldAnswerKey, null, $question)) !== []) {
            $errors[] = sprintf('answerKey kann nicht geändert werden – davon hängen ab: %s.', implode(', ', $dependents));
        }
        if ($errors === [] && in_array($question->getQuestionType(), self::EXTERNAL_OPTION_TYPES, true) && $question->getOptions()->count() > 0) {
            $errors[] = sprintf('Fragetyp "%s" ist nur ohne eigene Optionen möglich – zuerst die Optionen löschen.', $question->getQuestionType());
        }

        if ($errors !== []) {
            $this->em->refresh($question);

            return ['errors' => $errors];
        }

        $this->em->flush();

        return ['question' => $question, 'errors' => []];
    }

    /** @return list<string> */
    public function deleteQuestion(PartnerWizardQuestion $question): array
    {
        $client = $question->getStep()?->getClient();
        $dependents = $this->dependentQuestions($client, $question->getAnswerKey(), null, $question);
        if ($dependents !== []) {
            return [sprintf('Frage kann nicht gelöscht werden – davon hängen ab: %s. Zuerst dort "dependsOnAnswerKey" ändern.', implode(', ', $dependents))];
        }

        $question->getStep()?->getQuestions()->removeElement($question);
        $this->em->remove($question);
        $this->em->flush();

        return [];
    }

    // ------------------------------------------------------------------ Optionen

    /** @return array{option?: PartnerWizardQuestionOption, errors: list<string>} */
    public function createOption(PartnerWizardQuestion $question, array $data): array
    {
        if (in_array($question->getQuestionType(), self::EXTERNAL_OPTION_TYPES, true)) {
            return ['errors' => [sprintf('Fragetyp "%s" nutzt die Auswahl aus der Client-Konfiguration – eigene Optionen sind nicht möglich.', $question->getQuestionType())]];
        }
        if ($question->getOptions()->count() >= self::MAX_OPTIONS) {
            return ['errors' => [sprintf('Maximal %d Optionen pro Frage.', self::MAX_OPTIONS)]];
        }

        $option = new PartnerWizardQuestionOption();
        $errors = $this->applyOptionData($option, $data, true, $question);
        if ($errors !== []) {
            return ['errors' => $errors];
        }
        if (!array_key_exists('priority', $data)) {
            $max = 0;
            foreach ($question->getOptions() as $existing) {
                $max = max($max, $existing->getPriority());
            }
            $option->setPriority($max + 10);
        }

        $option->setQuestion($question);
        $question->getOptions()->add($option);
        $this->em->persist($option);
        $this->em->flush();

        return ['option' => $option, 'errors' => []];
    }

    /** @return array{option?: PartnerWizardQuestionOption, errors: list<string>} */
    public function updateOption(PartnerWizardQuestionOption $option, array $data): array
    {
        $question = $option->getQuestion();
        $oldValue = $option->getValue();
        $errors = $this->applyOptionData($option, $data, false, $question);

        if ($errors === [] && $oldValue !== $option->getValue() && $question
            && ($dependents = $this->dependentQuestions($question->getStep()?->getClient(), $question->getAnswerKey(), $oldValue, null)) !== []) {
            $errors[] = sprintf('Wert kann nicht geändert werden – davon hängen ab: %s.', implode(', ', $dependents));
        }

        if ($errors !== []) {
            $this->em->refresh($option);

            return ['errors' => $errors];
        }

        $this->em->flush();

        return ['option' => $option, 'errors' => []];
    }

    /** @return list<string> */
    public function deleteOption(PartnerWizardQuestionOption $option): array
    {
        $question = $option->getQuestion();
        if ($question) {
            $dependents = $this->dependentQuestions($question->getStep()?->getClient(), $question->getAnswerKey(), $option->getValue(), null);
            if ($dependents !== []) {
                return [sprintf('Option kann nicht gelöscht werden – davon hängen ab: %s.', implode(', ', $dependents))];
            }
            $question->getOptions()->removeElement($option);
        }

        $this->em->remove($option);
        $this->em->flush();

        return [];
    }

    // ------------------------------------------------------------------ Lookup

    public function findQuestion(PartnerWizardClient $client, int $id): ?PartnerWizardQuestion
    {
        $question = $this->em->getRepository(PartnerWizardQuestion::class)->find($id);

        return $question instanceof PartnerWizardQuestion && $question->getStep()?->getClient() === $client ? $question : null;
    }

    public function findOption(PartnerWizardClient $client, int $id): ?PartnerWizardQuestionOption
    {
        $option = $this->em->getRepository(PartnerWizardQuestionOption::class)->find($id);

        return $option instanceof PartnerWizardQuestionOption && $option->getQuestion()?->getStep()?->getClient() === $client ? $option : null;
    }

    // ------------------------------------------------------------------ intern

    /** @return list<string> */
    private function applyQuestionData(?PartnerWizardClient $client, PartnerWizardQuestion $question, array $data, bool $isNew): array
    {
        $errors = [];
        $allowed = ['stepId', 'answerKey', 'title', 'helpText', 'questionType', 'multiSelect', 'fieldType', 'required', 'dependsOnAnswerKey', 'dependsOnValue', 'priority', 'active', 'options'];
        foreach (array_diff(array_keys($data), $allowed) as $unknown) {
            $errors[] = sprintf('Unbekanntes Feld "%s".', $unknown);
        }

        if (array_key_exists('stepId', $data)) {
            $step = $this->isInt($data['stepId']) ? $this->em->getRepository(PartnerWizardStep::class)->find((int) $data['stepId']) : null;
            if (!$step instanceof PartnerWizardStep || $step->getClient() !== $client) {
                $errors[] = 'Schritt (stepId) nicht gefunden.';
            } elseif (!in_array($step->getType(), ['questions', 'contact'], true)) {
                $errors[] = sprintf('In Schritte vom Typ "%s" können keine Fragen gelegt werden.', $step->getType());
            } else {
                $question->getStep()?->getQuestions()->removeElement($question);
                $question->setStep($step);
                $step->getQuestions()->add($question);
            }
        }

        if (array_key_exists('answerKey', $data)) {
            $key = is_string($data['answerKey']) ? trim($data['answerKey']) : '';
            if (!preg_match(self::KEY_PATTERN, $key) || strlen($key) > 40) {
                $errors[] = 'answerKey: nur Buchstaben, Ziffern und _ (mit Buchstabe beginnend), max. 40 Zeichen.';
            } elseif ($this->answerKeyTaken($client, $key, $question)) {
                $errors[] = sprintf('answerKey "%s" ist bei diesem Client schon vergeben.', $key);
            } else {
                $question->setAnswerKey($key);
            }
        }

        $this->applyString($data, 'title', 180, false, $errors, fn (?string $v) => $question->setTitle((string) $v));
        $this->applyString($data, 'helpText', null, true, $errors, fn (?string $v) => $question->setHelpText($v));
        $this->applyEnum($data, 'questionType', self::QUESTION_TYPES, false, $errors, fn (?string $v) => $question->setQuestionType((string) $v));
        $this->applyEnum($data, 'fieldType', self::FIELD_TYPES, true, $errors, fn (?string $v) => $question->setFieldType($v));
        $this->applyBool($data, 'multiSelect', $errors, fn (bool $v) => $question->setMultiSelect($v));
        $this->applyBool($data, 'required', $errors, fn (bool $v) => $question->setRequired($v));
        $this->applyBool($data, 'active', $errors, fn (bool $v) => $question->setActive($v));
        $this->applyInt($data, 'priority', false, $errors, fn (?int $v) => $question->setPriority((int) $v));

        if (array_key_exists('dependsOnAnswerKey', $data)) {
            $value = $data['dependsOnAnswerKey'];
            if ($value === null || $value === '') {
                $question->setDependsOnAnswerKey(null);
            } elseif (!is_string($value) || !preg_match(self::KEY_PATTERN, $value) || strlen($value) > 40) {
                $errors[] = 'dependsOnAnswerKey: ungültiger Schlüssel.';
            } elseif ($value === $question->getAnswerKey()) {
                $errors[] = 'dependsOnAnswerKey darf nicht auf die Frage selbst zeigen.';
            } elseif (!$this->answerKeyTaken($client, $value, $question) && !in_array($value, ['privacyAccepted'], true)) {
                $errors[] = sprintf('dependsOnAnswerKey "%s": keine Frage mit diesem answerKey vorhanden.', $value);
            } else {
                $question->setDependsOnAnswerKey($value);
            }
        }
        $this->applyString($data, 'dependsOnValue', 120, true, $errors, fn (?string $v) => $question->setDependsOnValue($v));

        if ($isNew && $question->getStep() === null && $errors === []) {
            $errors[] = 'Schritt (stepId) fehlt.';
        }
        if ($isNew && !array_key_exists('priority', $data) && $question->getStep()) {
            $max = 0;
            foreach ($question->getStep()->getQuestions() as $existing) {
                if ($existing !== $question) {
                    $max = max($max, $existing->getPriority());
                }
            }
            $question->setPriority($max + 10);
        }

        return $errors;
    }

    /** @return list<string> */
    private function applyOptionData(PartnerWizardQuestionOption $option, array $data, bool $isNew, ?PartnerWizardQuestion $question): array
    {
        $errors = [];
        $allowed = ['value', 'title', 'text', 'numericValue', 'priority', 'active'];
        foreach (array_diff(array_keys($data), $allowed) as $unknown) {
            $errors[] = sprintf('Unbekanntes Feld "%s".', $unknown);
        }
        if ($isNew) {
            foreach (['value', 'title'] as $required) {
                if (!array_key_exists($required, $data)) {
                    $errors[] = sprintf('"%s" fehlt.', $required);
                }
            }
        }

        if (array_key_exists('value', $data)) {
            $value = is_string($data['value']) || is_int($data['value']) ? trim((string) $data['value']) : '';
            if ($value === '' || mb_strlen($value) > 80 || preg_match('/[\x00-\x1F]/', $value)) {
                $errors[] = 'value: Pflichtfeld, max. 80 Zeichen.';
            } elseif ($question && $this->optionValueTaken($question, $value, $option)) {
                $errors[] = sprintf('value "%s" gibt es bei dieser Frage schon.', $value);
            } else {
                $option->setValue($value);
            }
        }

        $this->applyString($data, 'title', 140, false, $errors, fn (?string $v) => $option->setTitle((string) $v));
        $this->applyString($data, 'text', null, true, $errors, fn (?string $v) => $option->setText($v));
        $this->applyInt($data, 'numericValue', true, $errors, fn (?int $v) => $option->setNumericValue($v));
        $this->applyInt($data, 'priority', false, $errors, fn (?int $v) => $option->setPriority((int) $v));
        $this->applyBool($data, 'active', $errors, fn (bool $v) => $option->setActive($v));

        return $errors;
    }

    /**
     * Fragen, die per dependsOnAnswerKey (und optional dependsOnValue) an $answerKey hängen.
     *
     * @return list<string> answerKeys der abhängigen Fragen
     */
    private function dependentQuestions(?PartnerWizardClient $client, string $answerKey, ?string $value, ?PartnerWizardQuestion $exclude): array
    {
        $result = [];
        foreach ($this->clientQuestions($client) as $question) {
            if ($question === $exclude || $question->getDependsOnAnswerKey() !== $answerKey) {
                continue;
            }
            if ($value !== null && $question->getDependsOnValue() !== $value) {
                continue;
            }
            $result[] = $question->getAnswerKey();
        }

        return $result;
    }

    private function answerKeyTaken(?PartnerWizardClient $client, string $key, PartnerWizardQuestion $self): bool
    {
        foreach ($this->clientQuestions($client) as $question) {
            if ($question !== $self && $question->getAnswerKey() === $key) {
                return true;
            }
        }

        return false;
    }

    private function optionValueTaken(PartnerWizardQuestion $question, string $value, PartnerWizardQuestionOption $self): bool
    {
        foreach ($question->getOptions() as $option) {
            if ($option !== $self && $option->getValue() === $value) {
                return true;
            }
        }

        return false;
    }

    /** @return list<PartnerWizardQuestion> */
    private function clientQuestions(?PartnerWizardClient $client): array
    {
        if (!$client) {
            return [];
        }
        $questions = [];
        foreach ($this->em->getRepository(PartnerWizardStep::class)->findBy(['client' => $client]) as $step) {
            foreach ($step->getQuestions() as $question) {
                $questions[] = $question;
            }
        }

        return $questions;
    }

    private function applyString(array $data, string $field, ?int $maxLength, bool $nullable, array &$errors, callable $setter): void
    {
        if (!array_key_exists($field, $data)) {
            return;
        }
        $value = $data[$field];
        if ($value !== null && !is_string($value)) {
            $errors[] = sprintf('%s: muss Text sein.', $field);

            return;
        }
        $value = $value === null ? null : trim(strip_tags(str_replace(["\r\n", "\r"], "\n", $value)));
        if ($value === null || $value === '') {
            if (!$nullable) {
                $errors[] = sprintf('%s: darf nicht leer sein.', $field);

                return;
            }
            $value = null;
        }
        if ($value !== null && mb_strlen($value) > ($maxLength ?? self::MAX_TEXT_LENGTH)) {
            $errors[] = sprintf('%s: maximal %d Zeichen.', $field, $maxLength ?? self::MAX_TEXT_LENGTH);

            return;
        }
        $setter($value);
    }

    private function applyEnum(array $data, string $field, array $allowed, bool $nullable, array &$errors, callable $setter): void
    {
        if (!array_key_exists($field, $data)) {
            return;
        }
        $value = $data[$field];
        if (($value === null || $value === '') && $nullable) {
            $setter(null);

            return;
        }
        if (!is_string($value) || !in_array($value, $allowed, true)) {
            $errors[] = sprintf('%s: erlaubt sind %s.', $field, implode(', ', $allowed));

            return;
        }
        $setter($value);
    }

    private function applyBool(array $data, string $field, array &$errors, callable $setter): void
    {
        if (!array_key_exists($field, $data)) {
            return;
        }
        if (!is_bool($data[$field])) {
            $errors[] = sprintf('%s: muss true oder false sein.', $field);

            return;
        }
        $setter($data[$field]);
    }

    private function applyInt(array $data, string $field, bool $nullable, array &$errors, callable $setter): void
    {
        if (!array_key_exists($field, $data)) {
            return;
        }
        $value = $data[$field];
        if ($value === null && $nullable) {
            $setter(null);

            return;
        }
        if (!is_int($value) || $value < -1000000 || $value > 1000000) {
            $errors[] = sprintf('%s: muss eine ganze Zahl sein.', $field);

            return;
        }
        $setter($value);
    }

    private function isInt(mixed $value): bool
    {
        return is_int($value) || (is_string($value) && ctype_digit($value));
    }
}
