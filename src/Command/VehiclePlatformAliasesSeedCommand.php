<?php

namespace App\Command;

use App\Entity\VehiclePlatformAlias;
use App\Repository\VehiclePlatformAliasRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:vehicle-platform-aliases:seed',
    description: 'Legt Standard-Plattform-Aliase an (z.B. Fiat Ducato <-> Peugeot Boxer), die dem VehicleMatcher als Zusatzhinweis fuer geteilte Fahrzeugplattformen dienen.'
)]
final class VehiclePlatformAliasesSeedCommand extends Command
{
    /**
     * @var list<array{name: string, sourceBrand: string, sourceModel: ?string, aliasBrand: string, aliasModel: ?string}>
     */
    private const SEED_ALIASES = [
        ['name' => 'Sevel Transporter', 'sourceBrand' => 'Fiat', 'sourceModel' => 'Ducato', 'aliasBrand' => 'Peugeot', 'aliasModel' => 'Boxer'],
        ['name' => 'Sevel Transporter', 'sourceBrand' => 'Fiat', 'sourceModel' => 'Ducato', 'aliasBrand' => 'Citroën', 'aliasModel' => 'Jumper'],
        ['name' => 'Sevel Transporter', 'sourceBrand' => 'Fiat', 'sourceModel' => 'Ducato', 'aliasBrand' => 'Opel', 'aliasModel' => 'Movano'],
        ['name' => 'Sevel Transporter', 'sourceBrand' => 'Peugeot', 'sourceModel' => 'Boxer', 'aliasBrand' => 'Fiat', 'aliasModel' => 'Ducato'],
        ['name' => 'Sevel Transporter', 'sourceBrand' => 'Peugeot', 'sourceModel' => 'Boxer', 'aliasBrand' => 'Citroën', 'aliasModel' => 'Jumper'],
        ['name' => 'Sevel Transporter', 'sourceBrand' => 'Citroën', 'sourceModel' => 'Jumper', 'aliasBrand' => 'Fiat', 'aliasModel' => 'Ducato'],
        ['name' => 'Sevel Transporter', 'sourceBrand' => 'Citroën', 'sourceModel' => 'Jumper', 'aliasBrand' => 'Peugeot', 'aliasModel' => 'Boxer'],

        ['name' => 'Renault-Nissan Transporter', 'sourceBrand' => 'Renault', 'sourceModel' => 'Master', 'aliasBrand' => 'Opel', 'aliasModel' => 'Movano'],
        ['name' => 'Renault-Nissan Transporter', 'sourceBrand' => 'Renault', 'sourceModel' => 'Master', 'aliasBrand' => 'Nissan', 'aliasModel' => 'NV400'],
        ['name' => 'Renault-Nissan Transporter', 'sourceBrand' => 'Opel', 'sourceModel' => 'Movano', 'aliasBrand' => 'Renault', 'aliasModel' => 'Master'],

        ['name' => 'Ford/VW Kooperation', 'sourceBrand' => 'Ford', 'sourceModel' => 'Transit Custom', 'aliasBrand' => 'Volkswagen', 'aliasModel' => 'Transporter'],
        ['name' => 'Ford/VW Kooperation', 'sourceBrand' => 'Volkswagen', 'sourceModel' => 'Transporter', 'aliasBrand' => 'Ford', 'aliasModel' => 'Transit Custom'],

        ['name' => 'VW/MAN Kooperation', 'sourceBrand' => 'Volkswagen', 'sourceModel' => 'Crafter', 'aliasBrand' => 'MAN', 'aliasModel' => 'TGE'],
        ['name' => 'VW/MAN Kooperation', 'sourceBrand' => 'MAN', 'sourceModel' => 'TGE', 'aliasBrand' => 'Volkswagen', 'aliasModel' => 'Crafter'],

        ['name' => 'Mercedes/VW Kooperation', 'sourceBrand' => 'Mercedes', 'sourceModel' => 'Sprinter', 'aliasBrand' => 'Volkswagen', 'aliasModel' => 'Crafter'],
        ['name' => 'Mercedes/VW Kooperation', 'sourceBrand' => 'Volkswagen', 'sourceModel' => 'Crafter', 'aliasBrand' => 'Mercedes', 'aliasModel' => 'Sprinter'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly VehiclePlatformAliasRepository $vehiclePlatformAliasRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $created = 0;
        $skipped = 0;

        foreach (self::SEED_ALIASES as $seed) {
            $existing = $this->vehiclePlatformAliasRepository->findOneBy([
                'sourceBrand' => $seed['sourceBrand'],
                'sourceModel' => $seed['sourceModel'],
                'aliasBrand' => $seed['aliasBrand'],
                'aliasModel' => $seed['aliasModel'],
            ]);
            if (null !== $existing) {
                ++$skipped;
                continue;
            }

            $alias = new VehiclePlatformAlias();
            $alias->setName($seed['name']);
            $alias->setSourceBrand($seed['sourceBrand']);
            $alias->setSourceModel($seed['sourceModel']);
            $alias->setAliasBrand($seed['aliasBrand']);
            $alias->setAliasModel($seed['aliasModel']);
            $alias->setScoreWeight(0.75);
            $alias->setIsActive(true);

            $this->entityManager->persist($alias);
            ++$created;
        }

        $this->entityManager->flush();

        $io->success(sprintf('Plattform-Aliase: %d neu angelegt, %d bereits vorhanden.', $created, $skipped));

        return Command::SUCCESS;
    }
}
