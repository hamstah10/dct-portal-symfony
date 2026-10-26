<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\PerformanceFormula;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:performance:seed',
    description: 'Erstellt Standard-Leistungsformeln'
)]
final class CreateDefaultPerformanceFormulasCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $formulas = [
            [
                'name' => 'hp_per_liter',
                'label' => 'PS pro Liter',
                'formula' => '{horsepower} / {displacement}',
                'resultUnit' => 'PS/L',
                'requiredFields' => ['horsepower', 'displacement'],
            ],
            [
                'name' => 'torque_per_liter',
                'label' => 'Nm pro Liter',
                'formula' => '{torque} / {displacement}',
                'resultUnit' => 'Nm/L',
                'requiredFields' => ['torque', 'displacement'],
            ],
            [
                'name' => 'power_to_weight',
                'label' => 'PS pro Kg',
                'formula' => '{horsepower} / {weight}',
                'resultUnit' => 'PS/kg',
                'requiredFields' => ['horsepower', 'weight'],
            ],
            [
                'name' => 'torque_to_weight',
                'label' => 'Nm pro Kg',
                'formula' => '{torque} / {weight}',
                'resultUnit' => 'Nm/kg',
                'requiredFields' => ['torque', 'weight'],
            ],
            [
                'name' => 'hp_per_100cc',
                'label' => 'PS pro 100ccm',
                'formula' => '({horsepower} / {displacement}) * 0.1',
                'resultUnit' => 'PS/100ccm',
                'requiredFields' => ['horsepower', 'displacement'],
            ],
            [
                'name' => 'efficiency_ratio',
                'label' => 'Effizienzrate',
                'formula' => '({horsepower} * {torque}) / ({weight} * 1000)',
                'resultUnit' => 'Effizienz',
                'requiredFields' => ['horsepower', 'torque', 'weight'],
            ],
            [
                'name' => 'displacement_per_cylinder',
                'label' => 'Hubraum pro Zylinder',
                'formula' => '{displacement} / 4',
                'resultUnit' => 'ccm',
                'requiredFields' => ['displacement'],
            ],
        ];

        $repo = $this->entityManager->getRepository(PerformanceFormula::class);
        $sorting = 0;

        foreach ($formulas as $data) {
            $existing = $repo->findOneBy(['name' => $data['name']]);
            if ($existing) {
                $output->writeln("Formula '{$data['name']}' already exists, skipping");
                continue;
            }

            $formula = new PerformanceFormula();
            $formula->setName($data['name'])
                ->setLabel($data['label'])
                ->setFormula($data['formula'])
                ->setResultUnit($data['resultUnit'])
                ->setRequiredFields($data['requiredFields'])
                ->setSorting($sorting++)
                ->setActive(true);

            $this->entityManager->persist($formula);
            $output->writeln("Created formula: {$data['label']}");
        }

        $this->entityManager->flush();
        $output->writeln('Performance formulas created successfully');

        return Command::SUCCESS;
    }
}
