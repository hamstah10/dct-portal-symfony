<?php

namespace App\Controller;

use App\Service\Tankerkoenig\TankerkoenigApiClient;
use App\Service\Tankerkoenig\TankerkoenigApiException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EcoTuningCalculatorController extends AbstractController
{
    public function __construct(
        private readonly TankerkoenigApiClient $tankerkoenigApiClient,
    ) {
    }

    #[Route('/chiptuning/eco-tuning-calculator', name: 'app_eco_tuning_calculator', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('website/eco_tuning_calculator.html.twig', [
            'ecoTuningCalculatorCssVersion' => $this->getCssVersion(),
        ]);
    }

    #[Route('/chiptuning/eco-tuning-calculator/fuel-price', name: 'app_eco_tuning_calculator_fuel_price', methods: ['GET'])]
    public function fuelPrice(Request $request): JsonResponse
    {
        $latitude = (float) $request->query->get('lat', 0);
        $longitude = (float) $request->query->get('lng', 0);
        $fuelType = (string) $request->query->get('fuelType', 'diesel');

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180 || (0.0 === $latitude && 0.0 === $longitude)) {
            return $this->json([
                'ok' => false,
                'message' => 'Ungueltige Koordinaten.',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->tankerkoenigApiClient->findCheapestFuelPrice($latitude, $longitude, $fuelType);
        } catch (TankerkoenigApiException $exception) {
            return $this->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_GATEWAY);
        }

        return $this->json([
            'ok' => true,
            'price' => $result['price'],
            'fuelType' => $result['fuelType'],
            'station' => $result['station'],
            'license' => $result['license'],
            'source' => $result['source'],
        ]);
    }

    private function getCssVersion(): string
    {
        $projectDir = (string) $this->getParameter('kernel.project_dir');
        $path = $projectDir . '/public/styles/eco_tuning_calculator.css';

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
}
