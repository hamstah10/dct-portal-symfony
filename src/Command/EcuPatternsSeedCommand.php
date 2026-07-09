<?php

namespace App\Command;

use App\Entity\EcuDetectionPattern;
use App\Repository\EcuDetectionPatternRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ecu-patterns:seed',
    description: 'Legt Standard-Erkennungsmuster fuer die ECU-Erkennung an (zusaetzlich zu den fest eingebauten Regeln im EcuDetector).'
)]
final class EcuPatternsSeedCommand extends Command
{
    /**
     * @var list<array{name: string, type: string, pattern: string, isRegex: bool, normalizedValue: ?string}>
     */
    private const SEED_PATTERNS = [
        ['name' => 'Bosch', 'type' => EcuDetectionPattern::TYPE_MANUFACTURER, 'pattern' => 'Bosch', 'isRegex' => false, 'normalizedValue' => 'Bosch'],
        ['name' => 'Continental', 'type' => EcuDetectionPattern::TYPE_MANUFACTURER, 'pattern' => 'Continental', 'isRegex' => false, 'normalizedValue' => 'Continental'],
        ['name' => 'Delphi', 'type' => EcuDetectionPattern::TYPE_MANUFACTURER, 'pattern' => 'Delphi', 'isRegex' => false, 'normalizedValue' => 'Delphi'],
        ['name' => 'Denso', 'type' => EcuDetectionPattern::TYPE_MANUFACTURER, 'pattern' => 'Denso', 'isRegex' => false, 'normalizedValue' => 'Denso'],
        ['name' => 'Marelli', 'type' => EcuDetectionPattern::TYPE_MANUFACTURER, 'pattern' => 'Marelli', 'isRegex' => false, 'normalizedValue' => 'Marelli'],

        ['name' => 'MD1', 'type' => EcuDetectionPattern::TYPE_ECU_FAMILY, 'pattern' => '/\bMD1[A-Z0-9]{2,8}\b/i', 'isRegex' => true, 'normalizedValue' => 'MD1'],
        ['name' => 'MG1', 'type' => EcuDetectionPattern::TYPE_ECU_FAMILY, 'pattern' => '/\bMG1[A-Z0-9]{2,8}\b/i', 'isRegex' => true, 'normalizedValue' => 'MG1'],
        ['name' => 'EDC17', 'type' => EcuDetectionPattern::TYPE_ECU_FAMILY, 'pattern' => '/\bEDC17[A-Z0-9]{1,8}\b/i', 'isRegex' => true, 'normalizedValue' => 'EDC17'],
        ['name' => 'MED17', 'type' => EcuDetectionPattern::TYPE_ECU_FAMILY, 'pattern' => '/\bMED17[A-Z0-9.]{1,10}\b/i', 'isRegex' => true, 'normalizedValue' => 'MED17'],
        ['name' => 'SID', 'type' => EcuDetectionPattern::TYPE_ECU_FAMILY, 'pattern' => '/\bSID[0-9A-Z.]{2,8}\b/i', 'isRegex' => true, 'normalizedValue' => 'SID'],
        ['name' => 'DCM', 'type' => EcuDetectionPattern::TYPE_ECU_FAMILY, 'pattern' => '/\bDCM[0-9A-Z.]{1,8}\b/i', 'isRegex' => true, 'normalizedValue' => 'DCM'],
        ['name' => 'PCR', 'type' => EcuDetectionPattern::TYPE_ECU_FAMILY, 'pattern' => '/\bPCR[0-9A-Z.]{1,8}\b/i', 'isRegex' => true, 'normalizedValue' => 'PCR'],
        ['name' => 'SIMOS', 'type' => EcuDetectionPattern::TYPE_ECU_FAMILY, 'pattern' => '/\bSIMOS[0-9A-Z.]{1,8}\b/i', 'isRegex' => true, 'normalizedValue' => 'SIMOS'],

        ['name' => 'TC298', 'type' => EcuDetectionPattern::TYPE_MCU, 'pattern' => 'TC298', 'isRegex' => false, 'normalizedValue' => 'TC298'],
        ['name' => 'TC299', 'type' => EcuDetectionPattern::TYPE_MCU, 'pattern' => 'TC299', 'isRegex' => false, 'normalizedValue' => 'TC299'],

        ['name' => 'Bench', 'type' => EcuDetectionPattern::TYPE_READ_METHOD, 'pattern' => 'Bench', 'isRegex' => false, 'normalizedValue' => 'Bench'],
        ['name' => 'Boot', 'type' => EcuDetectionPattern::TYPE_READ_METHOD, 'pattern' => 'Boot', 'isRegex' => false, 'normalizedValue' => 'Boot'],
        ['name' => 'OBD', 'type' => EcuDetectionPattern::TYPE_READ_METHOD, 'pattern' => 'OBD', 'isRegex' => false, 'normalizedValue' => 'OBD'],

        ['name' => 'No RFT', 'type' => EcuDetectionPattern::TYPE_PROTOCOL, 'pattern' => 'No RFT', 'isRegex' => false, 'normalizedValue' => 'No RFT'],
        ['name' => 'DCT', 'type' => EcuDetectionPattern::TYPE_PROTOCOL, 'pattern' => 'DCT', 'isRegex' => false, 'normalizedValue' => 'DCT'],
        ['name' => 'TCU', 'type' => EcuDetectionPattern::TYPE_PROTOCOL, 'pattern' => 'TCU', 'isRegex' => false, 'normalizedValue' => 'TCU'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EcuDetectionPatternRepository $ecuDetectionPatternRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $created = 0;
        $skipped = 0;

        foreach (self::SEED_PATTERNS as $seed) {
            $existing = $this->ecuDetectionPatternRepository->findOneBy(['name' => $seed['name'], 'type' => $seed['type']]);
            if (null !== $existing) {
                ++$skipped;
                continue;
            }

            $pattern = new EcuDetectionPattern();
            $pattern->setName($seed['name']);
            $pattern->setType($seed['type']);
            $pattern->setPattern($seed['pattern']);
            $pattern->setIsRegex($seed['isRegex']);
            $pattern->setNormalizedValue($seed['normalizedValue']);
            $pattern->setScoreWeight(1.0);
            $pattern->setIsActive(true);

            $this->entityManager->persist($pattern);
            ++$created;
        }

        $this->entityManager->flush();

        $io->success(sprintf('Erkennungsmuster: %d neu angelegt, %d bereits vorhanden.', $created, $skipped));

        return Command::SUCCESS;
    }
}
