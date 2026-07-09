<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:vehicle-data:sync-ecu-manufacturer-master-logos',
    description: 'Uebernimmt Logos und Beschreibungen von ECU-Herstellern in die ECU-Hersteller-Masterdaten.',
)]
final class SyncEcuManufacturerMasterLogosCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->entityManager->getConnection();

        $updatedLogos = $this->syncLogos($connection);
        $updatedDescriptions = $this->syncDescriptions($connection);

        $output->writeln(sprintf('ECU-Hersteller-Master Logos aktualisiert: %d', $updatedLogos));
        $output->writeln(sprintf('ECU-Hersteller-Master Beschreibungen aktualisiert: %d', $updatedDescriptions));

        return Command::SUCCESS;
    }

    private function syncLogos(Connection $connection): int
    {
        return $connection->executeStatement("
            UPDATE tx_chiptuningdatabase_ecu_manufacturer_master emm
            INNER JOIN (
                SELECT
                    em.master_manufacturer AS master_id,
                    MAX(NULLIF(em.logo, '')) AS logo
                FROM tx_chiptuningdatabase_domain_model_ecumanufacturer em
                WHERE em.master_manufacturer IS NOT NULL
                  AND em.logo IS NOT NULL
                  AND em.logo <> ''
                GROUP BY em.master_manufacturer
            ) resolved ON resolved.master_id = emm.uid
            SET emm.logo = resolved.logo
            WHERE emm.logo IS NULL OR emm.logo = ''
        ");
    }

    private function syncDescriptions(Connection $connection): int
    {
        return $connection->executeStatement("
            UPDATE tx_chiptuningdatabase_ecu_manufacturer_master emm
            INNER JOIN (
                SELECT
                    em.master_manufacturer AS master_id,
                    MAX(NULLIF(em.description, '')) AS description
                FROM tx_chiptuningdatabase_domain_model_ecumanufacturer em
                WHERE em.master_manufacturer IS NOT NULL
                  AND em.description IS NOT NULL
                  AND em.description <> ''
                GROUP BY em.master_manufacturer
            ) resolved ON resolved.master_id = emm.uid
            SET emm.description = resolved.description
            WHERE emm.description IS NULL OR emm.description = ''
        ");
    }
}
