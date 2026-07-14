<?php

namespace App\Command;

use App\Entity\ToolManufacturer;
use App\Entity\VehicleType;
use App\Repository\ToolManufacturerRepository;
use App\Repository\VehicleTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardCombination;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardPackage;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardQuestion;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardQuestionOption;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardStep;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardTool;

#[AsCommand(
    name: 'app:partner-wizard:seed',
    description: 'Seed a demo Partner-Wizard-Client mit Tools, Paketen und Kombinationen.'
)]
final class PartnerWizardSeedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly VehicleTypeRepository $vehicleTypeRepository,
        private readonly ToolManufacturerRepository $toolManufacturerRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('client-id', null, InputOption::VALUE_OPTIONAL, 'Client-ID des Wizard-Mandanten.', 'no1')
            ->addOption('fresh', null, InputOption::VALUE_NONE, 'Vorhandene Tools/Pakete/Kombinationen des Clients vorher löschen und neu anlegen.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $clientId = trim((string) $input->getOption('client-id')) ?: 'tfd';
        $fresh = (bool) $input->getOption('fresh');

        $vehicleTypes = $this->indexBySlug($this->vehicleTypeRepository->findBy([], ['sorting' => 'ASC']));
        if ([] === $vehicleTypes) {
            $io->error('Keine Fahrzeugtypen in der Datenbank gefunden. Bitte zuerst die Fahrzeugdatenbank befüllen.');
            return Command::FAILURE;
        }

        $toolManufacturers = $this->indexByName($this->toolManufacturerRepository->findAll());

        $client = $this->entityManager->getRepository(PartnerWizardClient::class)->findOneBy(['clientId' => $clientId]);
        $isNewClient = !$client instanceof PartnerWizardClient;
        if (!$client instanceof PartnerWizardClient) {
            $client = new PartnerWizardClient();
            $client->setClientId($clientId);
        }

        $preset = $this->resolveClientPreset($clientId);
        $client
            ->setName($preset['name'])
            ->setHeadline($preset['headline'])
            ->setIntroText($preset['introText'])
            ->setPrimaryColor($preset['primaryColor'])
            ->setTheme($preset['theme'])
            ->setToolRequired(true)
            ->setDefaultVehiclePrice(399)
            ->setEnabledServices(['stage1', 'stage2', 'eco', 'adblue', 'dpf', 'agr', 'vmax', 'pops', 'hardcut', 'dsg', 'diagnose', 'coding'])
            ->setPrivacyNotice('Mit dem Absenden erklärst du dich einverstanden, dass wir deine Angaben zur Bearbeitung der Anfrage speichern und dich kontaktieren.')
            ->setToolDisclaimer('Die Tool-Empfehlung ist eine erste Orientierung. Vor dem Kauf müssen Fahrzeuge, Steuergeräte und Protokolle konkret geprüft werden.')
            ->setSendNotificationEmail(false)
            ->setActive(true);

        foreach ($vehicleTypes as $vehicleType) {
            $client->addEnabledVehicleCategory($vehicleType);
        }
        foreach ($toolManufacturers as $toolManufacturer) {
            $client->addEnabledCurrentTool($toolManufacturer);
        }

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        if ($fresh) {
            foreach ($client->getTools() as $tool) {
                $this->entityManager->remove($tool);
            }
            foreach ($client->getPackages() as $package) {
                $this->entityManager->remove($package);
            }
            foreach ($this->entityManager->getRepository(PartnerWizardCombination::class)->findBy(['client' => $client]) as $combination) {
                $this->entityManager->remove($combination);
            }
            foreach ($this->entityManager->getRepository(PartnerWizardStep::class)->findBy(['client' => $client]) as $step) {
                $this->entityManager->remove($step);
            }
            $this->entityManager->flush();
        }

        $tools = $this->seedTools($client, $vehicleTypes);
        $packageCount = $this->seedPackages($client, $vehicleTypes);
        $combinationCount = $this->seedCombinations($client, $vehicleTypes, $tools);
        $stepCount = $this->seedSteps($client);

        $this->entityManager->flush();

        $io->success(sprintf(
            '%s Partner-Wizard-Client "%s" (%s) mit %d Tools, %d Paketen, %d Kombinationen und %d Schritten.',
            $isNewClient ? 'Neu angelegt:' : 'Aktualisiert:',
            $client->getName(),
            $client->getClientId(),
            count($tools),
            $packageCount,
            $combinationCount,
            $stepCount
        ));

        return Command::SUCCESS;
    }

    /**
     * @return array{name: string, headline: string, introText: string, primaryColor: string, theme: string}
     */
    private function resolveClientPreset(string $clientId): array
    {
        if ('no1' === mb_strtolower(trim($clientId))) {
            return [
                'name' => 'No1 Tuningfiles',
                'headline' => 'Chiptuning Händler werden',
                'introText' => 'Finde heraus, welches Partner-Modell und welche Tool-Richtung zu dir passt.',
                'primaryColor' => '#E31E24',
                'theme' => 'light',
            ];
        }

        return [
            'name' => 'TFD',
            'headline' => 'Chiptuning Händler werden',
            'introText' => 'Finde heraus, welches Partner-Modell und welche Tool-Richtung zu dir passt.',
            'primaryColor' => '#b4e600',
            'theme' => 'dark',
        ];
    }

    /**
     * @param array<string, VehicleType> $vehicleTypes
     * @return array<string, PartnerWizardTool>
     */
    private function seedTools(PartnerWizardClient $client, array $vehicleTypes): array
    {
        $definitions = [
            'alientech' => [
                'name' => 'Alientech',
                'description' => 'Universelles Master-Tool mit breiter Fahrzeugabdeckung für PKW und LKW.',
                'level' => 'universal',
                'vehicleSlugs' => ['pkw', 'lkw'],
                'services' => ['stage1', 'stage2', 'eco', 'dpf', 'adblue', 'agr'],
                'priority' => 10,
            ],
            'autotuner' => [
                'name' => 'Autotuner',
                'description' => 'Einstieg als Slave-Partner, ideal für Werkstätten ohne eigenes Master-Tool.',
                'level' => 'slave',
                'vehicleSlugs' => ['pkw'],
                'services' => ['stage1', 'stage2', 'dsg'],
                'priority' => 20,
            ],
            'dimsport' => [
                'name' => 'Dimsport',
                'description' => 'Master-Tool für PKW und Motorrad mit Fokus auf Performance-Stufen.',
                'level' => 'master',
                'vehicleSlugs' => ['pkw', 'motorrad'],
                'services' => ['stage1', 'stage2', 'vmax', 'hardcut'],
                'priority' => 30,
            ],
            'cmd-flash' => [
                'name' => 'CMD Flash',
                'description' => 'Universaltool mit Schwerpunkt LKW und Agrar, starke Abdeckung bei AdBlue/DPF/AGR.',
                'level' => 'universal',
                'vehicleSlugs' => ['lkw', 'agrar'],
                'services' => ['stage1', 'adblue', 'dpf', 'agr'],
                'priority' => 40,
            ],
            'magic-motorsport' => [
                'name' => 'Magic Motorsport',
                'description' => 'Spezialtool für Marine- und Freizeitfahrzeuge (Boot, Jetski).',
                'level' => 'special',
                'vehicleSlugs' => ['boat', 'jetski'],
                'services' => ['stage1', 'pops'],
                'priority' => 50,
            ],
        ];

        $tools = [];
        foreach ($definitions as $toolKey => $definition) {
            $tool = null;
            foreach ($client->getTools() as $existing) {
                if ($existing->getToolKey() === $toolKey) {
                    $tool = $existing;
                    break;
                }
            }

            if (!$tool instanceof PartnerWizardTool) {
                $tool = new PartnerWizardTool();
                $tool->setClient($client);
                $tool->setToolKey($toolKey);
            }

            $tool
                ->setName($definition['name'])
                ->setDescription($definition['description'])
                ->setLevel($definition['level'])
                ->setRecommendedForServices($definition['services'])
                ->setPriority($definition['priority'])
                ->setActive(true);

            foreach ($tool->getRecommendedForVehicleCategories()->toArray() as $vehicleType) {
                $tool->removeRecommendedForVehicleCategory($vehicleType);
            }
            foreach ($definition['vehicleSlugs'] as $slug) {
                if (isset($vehicleTypes[$slug])) {
                    $tool->addRecommendedForVehicleCategory($vehicleTypes[$slug]);
                }
            }

            $this->entityManager->persist($tool);
            $tools[$toolKey] = $tool;
        }

        return $tools;
    }

    /**
     * @param array<string, VehicleType> $vehicleTypes
     */
    private function seedPackages(PartnerWizardClient $client, array $vehicleTypes): int
    {
        $definitions = [
            'tool_starter' => [
                'title' => 'Tool Starter',
                'description' => 'Erst Tool-Beratung und Protokollprüfung, danach Händlerfreischaltung.',
                'requiredStatus' => 'tool_required',
                'min' => 0,
                'max' => 999,
                'goals' => [],
                'experience' => [],
                'vehicleSlugs' => [],
                'features' => ['Kostenlose Tool-Beratung', 'Protokoll- und Fahrzeugprüfung', 'Einstieg ohne Vorkenntnisse'],
                'cta' => 'Tool-Beratung anfragen',
                'priority' => 10,
            ],
            'full_package' => [
                'title' => 'Händler-Website Komplettpaket',
                'description' => 'Für deinen Start als Chiptuning-Händler empfehlen wir dir eine komplette Website mit integriertem Chiptuning-Konfigurator. Damit können Kunden direkt Fahrzeuge auswählen, Leistungsdaten ansehen und Anfragen senden.',
                'requiredStatus' => 'any',
                'min' => 0,
                'max' => 999,
                'goals' => [],
                'experience' => [],
                'vehicleSlugs' => [],
                'features' => ['Website und Konfigurator', 'Schneller Start', 'Einheitliche Händlerpräsenz'],
                'cta' => 'Website-Paket anfragen',
                'priority' => 15,
            ],
            'website_only' => [
                'title' => 'Händler-Website',
                'description' => 'Du brauchst zuerst einen professionellen Online-Auftritt. Die Website kann später jederzeit um den Chiptuning-Konfigurator erweitert werden.',
                'requiredStatus' => 'any',
                'min' => 0,
                'max' => 999,
                'goals' => [],
                'experience' => [],
                'vehicleSlugs' => [],
                'features' => ['Händler-Website', 'Kontakt- und Leistungsseiten', 'Für den reinen Webauftritt'],
                'cta' => 'Website anfragen',
                'priority' => 16,
            ],
            'configurator_only' => [
                'title' => 'Chiptuning-Konfigurator Widget',
                'description' => 'Du hast bereits eine Website. Der Konfigurator kann als Widget oder API-Anbindung integriert werden und erweitert deine Seite um Fahrzeugauswahl, Leistungsdaten und Anfragefunktion.',
                'requiredStatus' => 'any',
                'min' => 0,
                'max' => 999,
                'goals' => [],
                'experience' => [],
                'vehicleSlugs' => [],
                'features' => ['Konfigurator-Widget', 'Einbindung in bestehende Seiten', 'Fahrzeug- und Leistungsanfragen'],
                'cta' => 'Konfigurator anfragen',
                'priority' => 17,
            ],
            'workshop_slave_partner' => [
                'title' => 'Werkstatt / Slave Partner',
                'description' => 'Für Werkstätten und Händler mit Kundenkontakt und eigenem Tool.',
                'requiredStatus' => 'ready',
                'min' => 0,
                'max' => 29,
                'goals' => ['income'],
                'experience' => ['beginner', 'intermediate'],
                'vehicleSlugs' => ['pkw'],
                'features' => ['Zugang zum Partnerportal', 'Freigeschaltete Stage-Dateien', 'Support per Ticket'],
                'cta' => 'Händleranfrage senden',
                'priority' => 20,
            ],
            'preferred_dealer' => [
                'title' => 'Preferred Dealer Partner',
                'description' => 'Für etablierte Händler mit hohem Fahrzeugaufkommen und breitem Leistungsangebot.',
                'requiredStatus' => 'ready',
                'min' => 30,
                'max' => 999,
                'goals' => ['income', 'growth'],
                'experience' => ['advanced'],
                'vehicleSlugs' => ['pkw', 'lkw', 'motorrad'],
                'features' => ['Priorisierter Support', 'Rabattstaffel ab Volumen', 'Frühzugriff auf neue Protokolle'],
                'cta' => 'Preferred-Status anfragen',
                'priority' => 30,
            ],
        ];

        foreach ($definitions as $packageKey => $definition) {
            $package = null;
            foreach ($client->getPackages() as $existing) {
                if ($existing->getPackageKey() === $packageKey) {
                    $package = $existing;
                    break;
                }
            }

            if (!$package instanceof PartnerWizardPackage) {
                $package = new PartnerWizardPackage();
                $package->setClient($client);
                $package->setPackageKey($packageKey);
            }

            $package
                ->setTitle($definition['title'])
                ->setDescription($definition['description'])
                ->setRequiredStatus($definition['requiredStatus'])
                ->setMinVehiclesPerMonth($definition['min'])
                ->setMaxVehiclesPerMonth($definition['max'])
                ->setRequiredGoals($definition['goals'])
                ->setRequiredExperience($definition['experience'])
                ->setFeatures($definition['features'])
                ->setCtaLabel($definition['cta'])
                ->setPriority($definition['priority'])
                ->setActive(true);

            foreach ($package->getRecommendedVehicleCategories()->toArray() as $vehicleType) {
                $package->removeRecommendedVehicleCategory($vehicleType);
            }
            foreach ($definition['vehicleSlugs'] as $slug) {
                if (isset($vehicleTypes[$slug])) {
                    $package->addRecommendedVehicleCategory($vehicleTypes[$slug]);
                }
            }

            $this->entityManager->persist($package);
        }

        return count($definitions);
    }

    /**
     * @param array<string, VehicleType> $vehicleTypes
     * @param array<string, PartnerWizardTool> $tools
     */
    private function seedCombinations(PartnerWizardClient $client, array $vehicleTypes, array $tools): int
    {
        $definitions = [
            'LKW & Agrar Spezialist' => [
                'vehicleSlugs' => ['lkw', 'agrar'],
                'services' => ['stage1', 'adblue', 'dpf', 'agr'],
                'toolKeys' => ['cmd-flash'],
                'priority' => 10,
            ],
            'Marine & Freizeit' => [
                'vehicleSlugs' => ['boat', 'jetski'],
                'services' => ['stage1', 'pops'],
                'toolKeys' => ['magic-motorsport'],
                'priority' => 20,
            ],
        ];

        $existingCombinations = $this->entityManager->getRepository(PartnerWizardCombination::class)->findBy(['client' => $client]);

        foreach ($definitions as $name => $definition) {
            $combination = null;
            foreach ($existingCombinations as $existing) {
                if ($existing->getName() === $name) {
                    $combination = $existing;
                    break;
                }
            }

            if (!$combination instanceof PartnerWizardCombination) {
                $combination = new PartnerWizardCombination();
                $combination->setClient($client);
                $combination->setName($name);
            }

            $combination
                ->setServices($definition['services'])
                ->setPriority($definition['priority'])
                ->setActive(true);

            foreach ($combination->getVehicleCategories()->toArray() as $vehicleType) {
                $combination->removeVehicleCategory($vehicleType);
            }
            foreach ($definition['vehicleSlugs'] as $slug) {
                if (isset($vehicleTypes[$slug])) {
                    $combination->addVehicleCategory($vehicleTypes[$slug]);
                }
            }

            foreach ($combination->getTools()->toArray() as $tool) {
                $combination->removeTool($tool);
            }
            foreach ($definition['toolKeys'] as $toolKey) {
                if (isset($tools[$toolKey])) {
                    $combination->addTool($tools[$toolKey]);
                }
            }

            $this->entityManager->persist($combination);
        }

        return count($definitions);
    }

    private function seedSteps(PartnerWizardClient $client): int
    {
        $stepDefinitions = [
            [
                'key' => 'entry', 'label' => 'Einstieg', 'type' => 'questions', 'priority' => 10,
                'questions' => [
                    [
                        'answerKey' => 'goal', 'title' => 'Was ist dein Ziel?', 'questionType' => 'generic',
                        'multiSelect' => false, 'priority' => 10,
                        'options' => [
                            ['value' => 'start', 'title' => 'Neu starten', 'text' => 'Ich möchte in das Thema Chiptuning einsteigen.'],
                            ['value' => 'workshop', 'title' => 'Werkstatt erweitern', 'text' => 'Ich habe Kundenkontakt und möchte Chiptuning anbieten.'],
                            ['value' => 'active', 'title' => 'Aktiver Tuner', 'text' => 'Ich möchte regelmäßig Fahrzeuge bearbeiten.'],
                            ['value' => 'network', 'title' => 'Händlernetz / Master', 'text' => 'Ich denke an Master, Slaves oder White-Label.'],
                        ],
                    ],
                    [
                        'answerKey' => 'experience', 'title' => 'Wie viel Erfahrung hast du?', 'questionType' => 'generic',
                        'multiSelect' => false, 'priority' => 20,
                        'options' => [
                            ['value' => 'none', 'title' => 'Keine Erfahrung', 'text' => 'Ich brauche einen geführten Einstieg.'],
                            ['value' => 'diagnosis', 'title' => 'Diagnose / Codierung', 'text' => 'Ich arbeite schon am Fahrzeug.'],
                            ['value' => 'workshop', 'title' => 'Werkstatt-Erfahrung', 'text' => 'Ich habe regelmäßig Kundenfahrzeuge.'],
                            ['value' => 'tuning', 'title' => 'Chiptuning-Erfahrung', 'text' => 'Ich lese/schreibe bereits Steuergeräte.'],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'vehicleTool', 'label' => 'Fahrzeug & Tool', 'type' => 'questions', 'priority' => 20,
                'questions' => [
                    [
                        'answerKey' => 'vehicles', 'title' => 'Welche Fahrzeuge möchtest du bearbeiten?', 'questionType' => 'vehicleType',
                        'multiSelect' => true, 'priority' => 10, 'options' => [],
                    ],
                    [
                        'answerKey' => 'tool', 'title' => 'Hast du bereits ein eigenes Tool?', 'questionType' => 'toolManufacturer',
                        'multiSelect' => false, 'priority' => 20,
                        'helpText' => 'Ohne eigenes Tuning-Tool ist keine direkte Händler-Aktivierung möglich. Dann geht es zuerst in die Tool-Beratung.',
                        'options' => [],
                    ],
                ],
            ],
            [
                'key' => 'offerVolume', 'label' => 'Angebot & Volumen', 'type' => 'questions', 'priority' => 30,
                'questions' => [
                    [
                        'answerKey' => 'services', 'title' => 'Welche Leistungen willst du anbieten?', 'questionType' => 'generic',
                        'multiSelect' => true, 'priority' => 10,
                        'options' => [
                            ['value' => 'stage1', 'title' => 'Stage 1', 'text' => 'Leistungsoptimierung für Kundenfahrzeuge.'],
                            ['value' => 'stage2', 'title' => 'Stage 2', 'text' => 'Erweiterte Optimierungen und Umbauten.'],
                            ['value' => 'eco', 'title' => 'Eco Tuning', 'text' => 'Verbrauchsoptimierte Abstimmung.'],
                            ['value' => 'adblue', 'title' => 'AdBlue OFF', 'text' => 'SCR/AdBlue-bezogene Anfragen.'],
                            ['value' => 'dpf', 'title' => 'DPF OFF', 'text' => 'DPF-bezogene Anfragen.'],
                            ['value' => 'agr', 'title' => 'AGR OFF', 'text' => 'AGR-bezogene Anfragen.'],
                            ['value' => 'vmax', 'title' => 'VMax OFF', 'text' => 'VMax-bezogene Anfragen.'],
                            ['value' => 'pops', 'title' => 'Pops & Bangs', 'text' => 'Sound- und Motorsport-Features.'],
                            ['value' => 'hardcut', 'title' => 'Hardcut', 'text' => 'Hardcut / Drehzahlbegrenzung.'],
                            ['value' => 'dsg', 'title' => 'DSG / Getriebe', 'text' => 'Getriebe- und DSG-Anpassungen.'],
                            ['value' => 'diagnose', 'title' => 'Diagnose', 'text' => 'Diagnose- und Fehlersuche-Anfragen.'],
                            ['value' => 'coding', 'title' => 'Codierung', 'text' => 'Codierungs- und Anpassungs-Anfragen.'],
                        ],
                    ],
                    [
                        'answerKey' => 'website', 'title' => 'Welche Website möchtest du anbieten?', 'questionType' => 'generic',
                        'multiSelect' => false, 'priority' => 20,
                        'options' => [
                            ['value' => 'full_package', 'title' => 'Website + Konfigurator', 'text' => 'Website und Konfigurator gemeinsam aufsetzen.'],
                            ['value' => 'website_only', 'title' => 'Nur Website', 'text' => 'Händler-Website ohne Konfigurator.'],
                            ['value' => 'configurator_only', 'title' => 'Nur Chiptuning-Konfigurator', 'text' => 'Widget für die Fahrzeug- und Leistungsanfrage.'],
                            ['value' => 'none', 'title' => 'Nicht benötigt', 'text' => 'Ich brauche aktuell keine Website-Empfehlung.'],
                        ],
                    ],
                    [
                        'answerKey' => 'volume', 'title' => 'Wie viele Fahrzeuge erwartest du?', 'questionType' => 'volume',
                        'multiSelect' => false, 'priority' => 30,
                        'options' => [
                            ['value' => 'low', 'title' => '1–5 Fahrzeuge / Monat', 'text' => 'Kleiner Start oder gelegentliche Anfragen.', 'numericValue' => 3],
                            ['value' => 'medium', 'title' => '6–20 Fahrzeuge / Monat', 'text' => 'Regelmäßiges Geschäft mit Wachstum.', 'numericValue' => 12],
                            ['value' => 'high', 'title' => '20+ Fahrzeuge / Monat', 'text' => 'Professioneller Betrieb mit Volumen.', 'numericValue' => 25],
                            ['value' => 'network', 'title' => 'Händler / Netzwerk', 'text' => 'Mehrere Standorte, Slaves oder Partner.', 'numericValue' => 40],
                        ],
                    ],
                ],
            ],
            ['key' => 'recommendation', 'label' => 'Empfehlung', 'type' => 'recommendation', 'priority' => 40, 'questions' => []],
            [
                'key' => 'contact', 'label' => 'Kontakt', 'type' => 'contact', 'priority' => 50,
                'questions' => [
                    ['answerKey' => 'name', 'title' => 'Name', 'questionType' => 'generic', 'multiSelect' => false, 'priority' => 10, 'fieldType' => 'text', 'required' => true, 'options' => []],
                    ['answerKey' => 'company', 'title' => 'Firma', 'questionType' => 'generic', 'multiSelect' => false, 'priority' => 20, 'fieldType' => 'text', 'required' => false, 'options' => []],
                    ['answerKey' => 'email', 'title' => 'E-Mail', 'questionType' => 'generic', 'multiSelect' => false, 'priority' => 30, 'fieldType' => 'email', 'required' => true, 'options' => []],
                    ['answerKey' => 'phone', 'title' => 'Telefon', 'questionType' => 'generic', 'multiSelect' => false, 'priority' => 40, 'fieldType' => 'text', 'required' => false, 'options' => []],
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
                        ->setNumericValue($optionDef['numericValue'] ?? null)
                        ->setPriority($optionPriority)
                        ->setActive(true);

                    $this->entityManager->persist($option);
                    $optionPriority += 10;
                }
            }
        }

        return count($stepDefinitions);
    }

    /**
     * @param VehicleType[] $vehicleTypes
     * @return array<string, VehicleType>
     */
    private function indexBySlug(array $vehicleTypes): array
    {
        $result = [];
        foreach ($vehicleTypes as $vehicleType) {
            $slug = $vehicleType->getSlug();
            if (null !== $slug && '' !== $slug) {
                $result[$slug] = $vehicleType;
            }
        }

        return $result;
    }

    /**
     * @param ToolManufacturer[] $toolManufacturers
     * @return array<string, ToolManufacturer>
     */
    private function indexByName(array $toolManufacturers): array
    {
        $result = [];
        foreach ($toolManufacturers as $toolManufacturer) {
            $result[$toolManufacturer->getName()] = $toolManufacturer;
        }

        return $result;
    }
}
