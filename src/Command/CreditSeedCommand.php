<?php

namespace App\Command;

use App\Entity\CreditPackage;
use App\Entity\Kunde;
use App\Entity\KundeCreditTransaction;
use App\Repository\CreditPackageRepository;
use App\Repository\KundeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:credits:seed',
    description: 'Seed credit topup transactions for statistics (last N months).'
)]
final class CreditSeedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly KundeRepository $kundeRepository,
        private readonly CreditPackageRepository $packageRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('months', null, InputOption::VALUE_OPTIONAL, 'How many months back (including current month).', 12)
            ->addOption('per-month', null, InputOption::VALUE_OPTIONAL, 'Transactions per month.', 3)
            ->addOption('customer-id', null, InputOption::VALUE_OPTIONAL, 'Kunde ID (optional).')
            ->addOption('tax-rate', null, InputOption::VALUE_OPTIONAL, 'MwSt in Prozent.', 19)
            ->addOption('payment-mode', null, InputOption::VALUE_OPTIONAL, 'paypal|mixed', 'paypal')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be created without persisting.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $months = max(1, (int) $input->getOption('months'));
        $perMonth = max(1, (int) $input->getOption('per-month'));
        $taxRate = (float) $input->getOption('tax-rate');
        if ($taxRate < 0) {
            $taxRate = 0.0;
        }

        $paymentMode = strtolower(trim((string) $input->getOption('payment-mode')));
        if (!in_array($paymentMode, ['paypal', 'mixed'], true)) {
            $paymentMode = 'paypal';
        }

        $kunde = $this->resolveKunde($input, $io);
        if (!$kunde instanceof Kunde) {
            return Command::FAILURE;
        }

        $packages = array_filter(
            $this->packageRepository->findBy([], ['credits' => 'ASC']),
            static fn (CreditPackage $package): bool => $package->getCredits() > 0 && (float) $package->getPrice() > 0
        );

        if ([] === $packages) {
            $io->error('Keine Credit-Pakete mit Preis gefunden. Bitte zuerst Pakete pflegen.');
            return Command::FAILURE;
        }

        $now = new \DateTimeImmutable('now');
        $transactions = [];

        for ($i = $months - 1; $i >= 0; --$i) {
            $monthStart = (new \DateTimeImmutable('first day of this month'))->modify(sprintf('-%d months', $i))->setTime(0, 0);
            $monthEnd = $monthStart->modify('last day of this month')->setTime(23, 59, 59);

            for ($j = 0; $j < $perMonth; ++$j) {
                $package = $packages[array_rand($packages)];
                $credits = $package->getCredits();
                $priceNet = (float) $package->getPrice();
                $priceGross = $priceNet * (1 + ($taxRate / 100));

                $paymentMethod = $paymentMode === 'mixed'
                    ? (random_int(1, 100) <= 75 ? 'paypal' : 'bank_transfer')
                    : 'paypal';

                $paymentFee = null;
                if ('paypal' === $paymentMethod) {
                    $paymentFee = ($priceGross * 0.0179) + 0.35;
                }

                $createdAt = $this->randomDateBetween($monthStart, $monthEnd, $now);

                $transactions[] = [
                    'createdAt' => $createdAt,
                    'credits' => $credits,
                    'priceNet' => $priceNet,
                    'priceGross' => $priceGross,
                    'taxRate' => $taxRate,
                    'paymentMethod' => $paymentMethod,
                    'paymentFee' => $paymentFee,
                    'package' => $package,
                ];
            }
        }

        usort($transactions, static fn (array $a, array $b): int => $a['createdAt'] <=> $b['createdAt']);

        $balance = $kunde->getCredits();
        $createdCount = 0;
        foreach ($transactions as $row) {
            $balanceBefore = $balance;
            $balanceAfter = $balanceBefore + $row['credits'];
            $balance = $balanceAfter;

            $packageTitle = $row['package']->getTitle() ?? ('Paket #' . $row['package']->getId());
            $description = sprintf('Seed: %s (%d Credits)', $packageTitle, $row['credits']);

            $transaction = (new KundeCreditTransaction())
                ->setKunde($kunde)
                ->setAmount($row['credits'])
                ->setTransactionType(KundeCreditTransaction::TYPE_CUSTOMER_TOPUP)
                ->setDescription($description)
                ->setPaymentMethod($row['paymentMethod'])
                ->setPriceNet(number_format($row['priceNet'], 2, '.', ''))
                ->setPriceGross(number_format($row['priceGross'], 2, '.', ''))
                ->setTaxRate(number_format($row['taxRate'], 2, '.', ''))
                ->setPaymentFee(null !== $row['paymentFee'] ? number_format($row['paymentFee'], 2, '.', '') : null)
                ->setBalanceBefore($balanceBefore)
                ->setBalanceAfter($balanceAfter)
                ->setCreatedAt($row['createdAt']);

            if (!$input->getOption('dry-run')) {
                $this->entityManager->persist($transaction);
            }

            ++$createdCount;
        }

        if (!$input->getOption('dry-run')) {
            $kunde->setCredits($balance);
            $this->entityManager->persist($kunde);
            $this->entityManager->flush();
        }

        $io->success(sprintf(
            '%d Credit-Transaktionen fuer %s erstellt (%d Monate, %d pro Monat).',
            $createdCount,
            (string) $kunde,
            $months,
            $perMonth
        ));

        return Command::SUCCESS;
    }

    private function resolveKunde(InputInterface $input, SymfonyStyle $io): ?Kunde
    {
        $kundeId = (int) ($input->getOption('customer-id') ?? 0);
        if ($kundeId > 0) {
            $kunde = $this->kundeRepository->find($kundeId);
            if (!$kunde instanceof Kunde) {
                $io->error('Kunde nicht gefunden.');
                return null;
            }

            return $kunde;
        }

        $kunden = $this->kundeRepository->findBy([], ['id' => 'ASC']);
        if ([] === $kunden) {
            $io->error('Keine Kunden vorhanden.');
            return null;
        }

        foreach ($kunden as $candidate) {
            if (!$candidate instanceof Kunde) {
                continue;
            }

            $email = strtolower((string) $candidate->getEmail());
            $name = strtolower(trim((string) ($candidate->getVorname() . ' ' . $candidate->getNachname())));
            if (str_contains($email, 'test') || str_contains($email, 'demo') || str_contains($name, 'test')) {
                return $candidate;
            }
        }

        return $kunden[0] instanceof Kunde ? $kunden[0] : null;
    }

    private function randomDateBetween(\DateTimeImmutable $start, \DateTimeImmutable $end, \DateTimeImmutable $now): \DateTimeImmutable
    {
        $startTs = $start->getTimestamp();
        $endTs = min($end->getTimestamp(), $now->getTimestamp());
        if ($endTs <= $startTs) {
            return $start;
        }

        $rand = random_int($startTs, $endTs);
        return (new \DateTimeImmutable())->setTimestamp($rand);
    }
}
