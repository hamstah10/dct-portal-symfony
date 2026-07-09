<?php

namespace App\Controller;

use App\Entity\ProfitCalculator;
use App\Repository\ProfitCalculatorRepository;
use App\Repository\ProfitCalculatorPriceTierRepository;
use App\Repository\ProfitCalculatorVehicleTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfitCalculatorController extends AbstractController
{
    public function __construct(
        private readonly ProfitCalculatorRepository $calculatorRepository,
        private readonly ProfitCalculatorVehicleTypeRepository $vehicleTypeRepository,
        private readonly ProfitCalculatorPriceTierRepository $priceTierRepository,
    ) {
    }

    #[Route('/chiptuning/profitcalculator', name: 'app_profit_calculator', methods: ['GET'])]
    public function index(): Response
    {
        $calculator = $this->calculatorRepository->findOneBy([]);
        $vehicleTypes = $this->loadVehicleTypes($calculator);

        return $this->render('website/profit_calculator.html.twig', [
            'calculator' => $calculator,
            'vehicleTypes' => $vehicleTypes,
            'vatRate' => $calculator ? (float) $calculator->getVatRate() : 0.0,
            'priceTiers' => $this->priceTierRepository->findBy([], ['credits' => 'ASC']),
            'profitCalculatorCssVersion' => $this->getProfitCalculatorCssVersion(),
        ]);
    }

    private function getProfitCalculatorCssVersion(): string
    {
        $projectDir = (string) $this->getParameter('kernel.project_dir');
        $path = $projectDir . '/public/styles/profit_calculator.css';

        if (!is_file($path)) {
            return (string) time();
        }

        $hash = hash_file('sha1', $path);
        if (false === $hash) {
            $mtime = @filemtime($path);

            return false === $mtime ? (string) time() : (string) $mtime;
        }

        return substr($hash, 0, 12);
    }

    /**
     * @return array<int, \App\Entity\ProfitCalculatorVehicleType>
     */
    private function loadVehicleTypes(?ProfitCalculator $calculator): array
    {
        $qb = $this->vehicleTypeRepository->createQueryBuilder('vt')
            ->leftJoin('vt.options', 'opt')
            ->addSelect('opt')
            ->orderBy('vt.sorting', 'ASC')
            ->addOrderBy('vt.title', 'ASC')
            ->addOrderBy('opt.title', 'ASC');

        if ($calculator instanceof ProfitCalculator) {
            $qb->andWhere('vt.calculator = :calculator')
                ->setParameter('calculator', $calculator);
        }

        return $qb->getQuery()->getResult();
    }
}
