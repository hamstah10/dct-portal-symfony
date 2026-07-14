<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardQuestion;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardQuestionOption;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardStep;

#[AsCommand(
    name: 'app:partner-wizard:seed-dependency-demo',
    description: 'Seed einen zweiten Demo-Wizard-Client, der abhängige Fragen (dependsOnAnswerKey/dependsOnValue) zeigt.'
)]
final class PartnerWizardDependencyDemoSeedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('client-id', null, InputOption::VALUE_OPTIONAL, 'Client-ID des Demo-Mandanten.', 'demo-dependencies');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $clientId = trim((string) $input->getOption('client-id')) ?: 'demo-dependencies';

        $client = $this->entityManager->getRepository(PartnerWizardClient::class)->findOneBy(['clientId' => $clientId]);
        $isNewClient = !$client instanceof PartnerWizardClient;
        if (!$client instanceof PartnerWizardClient) {
            $client = new PartnerWizardClient();
            $client->setClientId($clientId);
        }

        $client
            ->setName('Demo: Abhängige Fragen')
            ->setHeadline('Demo-Wizard: abhängige Fragen')
            ->setIntroText('Dieser Wizard zeigt, wie Fragen sich in Abhängigkeit von vorherigen Antworten ein- und ausblenden.')
            ->setPrimaryColor('#4f8cff')
            ->setTheme('dark')
            ->setToolRequired(false)
            ->setDefaultVehiclePrice(0)
            ->setEnabledServices(['stage1', 'stage2', 'eco', 'dsg', 'diagnose'])
            ->setPrivacyNotice('Mit dem Absenden erklärst du dich einverstanden, dass wir deine Angaben zur Bearbeitung der Anfrage speichern und dich kontaktieren.')
            ->setToolDisclaimer('')
            ->setSendNotificationEmail(false)
            ->setActive(true);

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        $stepCount = $this->seedSteps($client);
        $this->entityManager->flush();

        $io->success(sprintf(
            '%s Demo-Wizard-Client "%s" (%s) mit %d Schritten.',
            $isNewClient ? 'Neu angelegt:' : 'Aktualisiert:',
            $client->getName(),
            $client->getClientId(),
            $stepCount
        ));

        return Command::SUCCESS;
    }

    private function seedSteps(PartnerWizardClient $client): int
    {
        $stepDefinitions = [
            [
                'key' => 'start', 'label' => 'Start', 'type' => 'questions', 'priority' => 10,
                'questions' => [
                    [
                        'answerKey' => 'hasTool', 'title' => 'Hast du bereits ein eigenes Tuning-Tool?', 'questionType' => 'generic',
                        'multiSelect' => false, 'priority' => 10,
                        'options' => [
                            ['value' => 'yes', 'title' => 'Ja, ich habe eins', 'text' => 'Ich arbeite bereits mit einem Tuning-Tool.'],
                            ['value' => 'no', 'title' => 'Nein, noch nicht', 'text' => 'Ich stehe noch am Anfang.'],
                        ],
                    ],
                    [
                        'answerKey' => 'toolBrand', 'title' => 'Welches Tool nutzt du aktuell?', 'questionType' => 'generic',
                        'multiSelect' => false, 'priority' => 20,
                        'helpText' => 'Beispiel für einfache Abhängigkeit: erscheint nur, wenn oben "Ja, ich habe eins" gewählt wurde.',
                        'dependsOnAnswerKey' => 'hasTool', 'dependsOnValue' => 'yes',
                        'options' => [
                            ['value' => 'alientech', 'title' => 'Alientech', 'text' => ''],
                            ['value' => 'autotuner', 'title' => 'Autotuner', 'text' => ''],
                            ['value' => 'dimsport', 'title' => 'Dimsport', 'text' => ''],
                            ['value' => 'other', 'title' => 'Ein anderes Tool', 'text' => ''],
                        ],
                    ],
                    [
                        'answerKey' => 'toolBrandOtherInfo', 'title' => 'Kein Problem – was brauchst du genau?', 'questionType' => 'generic',
                        'multiSelect' => false, 'priority' => 30,
                        'helpText' => 'Beispiel für verkettete Abhängigkeit: erscheint nur, wenn bei der vorherigen Frage "Ein anderes Tool" gewählt wurde.',
                        'dependsOnAnswerKey' => 'toolBrand', 'dependsOnValue' => 'other',
                        'options' => [
                            ['value' => 'advice', 'title' => 'Ich brauche Beratung', 'text' => ''],
                            ['value' => 'know_specs', 'title' => 'Ich kenne die technischen Daten bereits', 'text' => ''],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'services', 'label' => 'Leistungen', 'type' => 'questions', 'priority' => 20,
                'questions' => [
                    [
                        'answerKey' => 'interests', 'title' => 'Welche Leistungen interessieren dich?', 'questionType' => 'generic',
                        'multiSelect' => true, 'priority' => 10,
                        'options' => [
                            ['value' => 'stage1', 'title' => 'Stage 1', 'text' => ''],
                            ['value' => 'stage2', 'title' => 'Stage 2', 'text' => ''],
                            ['value' => 'eco', 'title' => 'Eco Tuning', 'text' => ''],
                            ['value' => 'dsg', 'title' => 'DSG / Getriebe', 'text' => ''],
                            ['value' => 'diagnose', 'title' => 'Diagnose', 'text' => ''],
                        ],
                    ],
                    [
                        'answerKey' => 'dsgDetail', 'title' => 'Welches Getriebe genau?', 'questionType' => 'generic',
                        'multiSelect' => false, 'priority' => 20,
                        'helpText' => 'Beispiel für Mehrfachauswahl-Abhängigkeit: erscheint nur, wenn oben "DSG / Getriebe" mit ausgewählt wurde.',
                        'dependsOnAnswerKey' => 'interests', 'dependsOnValue' => 'dsg',
                        'options' => [
                            ['value' => 'dq200', 'title' => 'DQ200', 'text' => ''],
                            ['value' => 'dq250', 'title' => 'DQ250', 'text' => ''],
                            ['value' => 'dq500', 'title' => 'DQ500', 'text' => ''],
                            ['value' => 'other', 'title' => 'Ein anderes Getriebe', 'text' => ''],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'contact', 'label' => 'Kontakt', 'type' => 'contact', 'priority' => 30,
                'questions' => [
                    ['answerKey' => 'name', 'title' => 'Name', 'questionType' => 'generic', 'multiSelect' => false, 'priority' => 10, 'fieldType' => 'text', 'required' => true, 'options' => []],
                    ['answerKey' => 'email', 'title' => 'E-Mail', 'questionType' => 'generic', 'multiSelect' => false, 'priority' => 20, 'fieldType' => 'email', 'required' => true, 'options' => []],
                    [
                        'answerKey' => 'wantsNewsletter', 'title' => 'Möchtest du unseren Newsletter erhalten?', 'questionType' => 'generic',
                        'multiSelect' => false, 'priority' => 30, 'fieldType' => 'checkbox', 'required' => false, 'options' => [],
                    ],
                    [
                        'answerKey' => 'newsletterTopics', 'title' => 'Welche Themen interessieren dich?', 'questionType' => 'generic',
                        'multiSelect' => true, 'priority' => 40, 'fieldType' => 'checkbox', 'required' => false,
                        'helpText' => 'Beispiel für Abhängigkeit im Kontakt-Schritt: erscheint nur, wenn die Newsletter-Checkbox oben aktiviert wurde.',
                        'dependsOnAnswerKey' => 'wantsNewsletter', 'dependsOnValue' => 'true',
                        'options' => [
                            ['value' => 'news', 'title' => 'Neuigkeiten', 'text' => ''],
                            ['value' => 'events', 'title' => 'Events & Workshops', 'text' => ''],
                            ['value' => 'tech', 'title' => 'Technik-Updates', 'text' => ''],
                        ],
                    ],
                    ['answerKey' => 'message', 'title' => 'Nachricht', 'questionType' => 'generic', 'multiSelect' => false, 'priority' => 50, 'fieldType' => 'textarea', 'required' => false, 'options' => []],
                    [
                        'answerKey' => 'privacyAccepted', 'title' => 'Mit dem Absenden erklärst du dich einverstanden, dass wir deine Angaben zur Bearbeitung der Anfrage speichern und dich kontaktieren.',
                        'questionType' => 'generic', 'multiSelect' => false, 'priority' => 60, 'fieldType' => 'checkbox', 'required' => true, 'options' => [],
                    ],
                ],
            ],
        ];

        $existingSteps = $this->entityManager->getRepository(PartnerWizardStep::class)->findBy(['client' => $client]);

        foreach ($stepDefinitions as $stepDef) {
            $step = null;
            foreach ($existingSteps as $existing) {
                if ($existing->getStepKey() === $stepDef['key']) {
                    $step = $existing;
                    break;
                }
            }

            if (!$step instanceof PartnerWizardStep) {
                $step = new PartnerWizardStep();
                $step->setClient($client);
                $step->setStepKey($stepDef['key']);
            }

            $step
                ->setLabel($stepDef['label'])
                ->setType($stepDef['type'])
                ->setPriority($stepDef['priority'])
                ->setActive(true);

            $this->entityManager->persist($step);

            $existingQuestions = $step->getQuestions()->toArray();
            foreach ($stepDef['questions'] as $questionDef) {
                $question = null;
                foreach ($existingQuestions as $existing) {
                    if ($existing->getAnswerKey() === $questionDef['answerKey']) {
                        $question = $existing;
                        break;
                    }
                }

                if (!$question instanceof PartnerWizardQuestion) {
                    $question = new PartnerWizardQuestion();
                    $question->setStep($step);
                    $question->setAnswerKey($questionDef['answerKey']);
                }

                $question
                    ->setTitle($questionDef['title'])
                    ->setHelpText($questionDef['helpText'] ?? null)
                    ->setQuestionType($questionDef['questionType'])
                    ->setMultiSelect($questionDef['multiSelect'])
                    ->setFieldType($questionDef['fieldType'] ?? null)
                    ->setRequired($questionDef['required'] ?? false)
                    ->setDependsOnAnswerKey($questionDef['dependsOnAnswerKey'] ?? null)
                    ->setDependsOnValue($questionDef['dependsOnValue'] ?? null)
                    ->setPriority($questionDef['priority'])
                    ->setActive(true);

                $this->entityManager->persist($question);

                $existingOptions = $question->getOptions()->toArray();
                $optionPriority = 10;
                foreach ($questionDef['options'] as $optionDef) {
                    $option = null;
                    foreach ($existingOptions as $existing) {
                        if ($existing->getValue() === $optionDef['value']) {
                            $option = $existing;
                            break;
                        }
                    }

                    if (!$option instanceof PartnerWizardQuestionOption) {
                        $option = new PartnerWizardQuestionOption();
                        $option->setQuestion($question);
                        $option->setValue($optionDef['value']);
                    }

                    $option
                        ->setTitle($optionDef['title'])
                        ->setText($optionDef['text'] ?? null)
                        ->setPriority($optionPriority)
                        ->setActive(true);

                    $this->entityManager->persist($option);
                    $optionPriority += 10;
                }
            }
        }

        return count($stepDefinitions);
    }
}
