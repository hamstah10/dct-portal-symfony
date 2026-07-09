<?php

namespace App\Command;

use App\Entity\BrandMaster;
use App\Service\BrandMasterImageResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:vehicle-data:sync-brand-master-images',
    description: 'Synchronisiert BrandMaster-Bilder mit lokalen Brand-Logos aus assets/icons/brands-cars/Icons.',
)]
final class SyncBrandMasterImagesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BrandMasterImageResolver $brandMasterImageResolver,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'only-missing',
            null,
            InputOption::VALUE_NONE,
            'Nur BrandMaster ohne Bildpfad und ohne MediaAsset aktualisieren.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $onlyMissing = (bool) $input->getOption('only-missing');
        $masters = $this->entityManager->getRepository(BrandMaster::class)->findBy([], ['name' => 'ASC']);
        $updated = 0;
        $connection = $this->entityManager->getConnection();

        foreach ($masters as $master) {
            if (!$master instanceof BrandMaster) {
                continue;
            }

            if ($onlyMissing && null !== $master->getResolvedImagePath()) {
                continue;
            }

            $resolved = $this->brandMasterImageResolver->resolveForType(
                $master->getVehicleType()?->getSlug(),
                $master->getSlug(),
                $master->getName(),
                $master->getImage()
            );
            if ($resolved === $master->getImage()) {
                continue;
            }

            $connection->update(
                'tx_chiptuningdatabase_brand_master',
                ['image' => $resolved],
                ['uid' => $master->getId()]
            );
            ++$updated;
        }

        $output->writeln(sprintf('BrandMaster aktualisiert: %d', $updated));

        return Command::SUCCESS;
    }
}
