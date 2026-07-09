<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:vehicle-data:sync-ecu-master-images',
    description: 'Uebernimmt vorhandene ECU-Bilder in die EcuMaster-Datensaetze.',
)]
final class SyncEcuMasterImagesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $updated = $this->syncImages($this->entityManager->getConnection());

        $output->writeln(sprintf('ECU-Master Bilder aktualisiert: %d', $updated));

        return Command::SUCCESS;
    }

    private function syncImages(Connection $connection): int
    {
        return $connection->executeStatement("
            UPDATE tx_chiptuningdatabase_ecu_master em
            INNER JOIN (
                SELECT
                    e.master_ecu AS master_id,
                    MAX(NULLIF(e.image, '')) AS image
                FROM tx_chiptuningdatabase_domain_model_ecu e
                WHERE e.master_ecu IS NOT NULL
                  AND e.image IS NOT NULL
                  AND e.image <> ''
                GROUP BY e.master_ecu
            ) resolved ON resolved.master_id = em.uid
            SET em.image = resolved.image
            WHERE em.image IS NULL OR em.image = ''
        ");
    }
}
