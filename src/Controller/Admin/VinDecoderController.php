<?php

namespace App\Controller\Admin;

use App\Repository\VinDecoderCacheRepository;
use App\Repository\VehicleBrandRepository;
use App\Service\VinDecoderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;

final class VinDecoderController extends AbstractController
{
    public function __construct(
        private readonly VinDecoderService $vinDecoderService,
        private readonly VinDecoderCacheRepository $vinDecoderCacheRepository,
        private readonly VehicleBrandRepository $vehicleBrandRepository,
    ) {
    }

    #[AdminRoute(path: '/vin-decoder', name: 'vin_decoder', options: ['methods' => ['GET', 'POST']])]
    public function index(Request $request): Response
    {
        $vinInput = (string) $request->request->get('vin', $request->query->get('vin', ''));
        $action = (string) $request->request->get('action', $request->query->get('action', ''));
        $requestVpic = 'vpic' === strtolower(trim($action))
            || '1' === (string) $request->request->get('vpic')
            || '1' === (string) $request->query->get('vpic');
        $result = null;
        $recentVehicles = $this->buildRecentVinList();
        $manufacturerLogo = null;

        if ('' !== trim($vinInput)) {
            if ($request->isMethod('POST')) {
                $csrfToken = (string) $request->request->get('_csrf_token');
                if (!$this->isCsrfTokenValid('admin_vin_decoder', $csrfToken)) {
                    $this->addFlash('danger', 'Ungültiges Formular-Token.');

                    return $this->redirectToRoute('admin_vin_decoder', ['vin' => $vinInput]);
                }
            }

            $result = $this->vinDecoderService->decode($vinInput);
            if ($requestVpic) {
                $vpic = $this->vinDecoderService->fetchVpic($vinInput, $result['modelYear'] ?? null);
                if (is_array($result)) {
                    $result['vpic'] = $vpic;
                }
            }
            $manufacturerLogo = $this->resolveManufacturerLogo($result);
        }

        return $this->render('admin/vin_decoder/index.html.twig', [
            'vinInput' => $vinInput,
            'result' => $result,
            'recentVehicles' => $recentVehicles,
            'manufacturerLogo' => $manufacturerLogo,
        ]);
    }

    #[AdminRoute(path: '/vin-decoder/export/{format}', name: 'vin_decoder_export', options: ['methods' => ['GET'], 'requirements' => ['format' => 'json|csv']])]
    public function export(Request $request, string $format): Response
    {
        $vinInput = (string) $request->query->get('vin', '');
        if ('' === trim($vinInput)) {
            $this->addFlash('warning', 'Bitte eine VIN eingeben, um zu exportieren.');

            return $this->redirectToRoute('admin_vin_decoder');
        }

        $result = $this->vinDecoderService->decode($vinInput);

        if ('json' === $format) {
            return new JsonResponse($result);
        }

        $csv = $this->buildCsv($result);
        $filename = sprintf('vin-decoder-%s.csv', date('Ymd-His'));

        return new Response($csv, Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => (new ResponseHeaderBag())->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            ),
        ]);
    }

    /**
     * @param array<string, mixed> $result
     */
    private function buildCsv(array $result): string
    {
        $handle = fopen('php://temp', 'r+');
        if (false === $handle) {
            return '';
        }

        fputcsv($handle, ['key', 'value'], ';');
        foreach ($result as $key => $value) {
            $encoded = $this->normalizeCsvValue($value);
            fputcsv($handle, [$key, $encoded], ';');
        }

        rewind($handle);
        $csvBody = stream_get_contents($handle);
        fclose($handle);

        return "\xEF\xBB\xBF" . ($csvBody ?: '');
    }

    private function normalizeCsvValue(mixed $value): string
    {
        if (null === $value) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }

        return (string) json_encode($value, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildRecentVinList(): array
    {
        $entries = $this->vinDecoderCacheRepository->findBy([], ['createdAt' => 'DESC'], 50);
        $list = [];

        foreach ($entries as $entry) {
            $payload = $entry->getPayload();
            $vpic = $payload['__vpic'] ?? null;
            $vpicValues = is_array($vpic) && isset($vpic['values']) && is_array($vpic['values']) ? $vpic['values'] : [];
            $list[] = [
                'vin' => $entry->getVin(),
                'make' => $this->normalizePayloadValue($payload['Make'] ?? ($vpicValues['make'] ?? null)),
                'model' => $this->normalizePayloadValue($payload['Model'] ?? ($vpicValues['model'] ?? null)),
                'modelYear' => $this->normalizePayloadValue($payload['Model Year'] ?? ($vpicValues['modelYear'] ?? null)),
                'manufacturer' => $this->normalizePayloadValue($payload['Manufacturer'] ?? ($vpicValues['manufacturer'] ?? null)),
                'plantCountry' => $this->normalizePayloadValue($payload['Plant Country'] ?? ($vpicValues['plantCountry'] ?? null)),
                'createdAt' => $entry->getCreatedAt(),
            ];
        }

        return $list;
    }

    private function normalizePayloadValue(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (is_string($value)) {
            $trimmed = trim($value);
            return '' === $trimmed ? null : $trimmed;
        }
        if (is_array($value)) {
            $items = [];
            foreach ($value as $item) {
                if (is_string($item)) {
                    $item = trim($item);
                    if ('' !== $item) {
                        $items[] = $item;
                    }
                    continue;
                }
                if (is_int($item) || is_float($item)) {
                    $items[] = (string) $item;
                }
            }
            return [] === $items ? null : implode(', ', $items);
        }

        return null;
    }

    private function resolveManufacturerLogo(?array $result): ?string
    {
        if (!is_array($result)) {
            return null;
        }

        $vinSpecific = $result['vinOverride'] ?? null;
        $make = '';
        if (is_array($vinSpecific)) {
            $make = trim((string) ($vinSpecific['make'] ?? ''));
            if ('' === $make) {
                $make = trim((string) ($vinSpecific['manufacturer'] ?? ''));
            }
        }
        if ('' === $make) {
            $make = trim((string) ($result['manufacturer'] ?? ''));
        }
        if ('' === $make) {
            return null;
        }

        $brand = $this->vehicleBrandRepository->createQueryBuilder('b')
            ->andWhere('LOWER(b.name) = :name')
            ->setParameter('name', strtolower($make))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (null === $brand) {
            return null;
        }

        $path = trim((string) $brand->getImage());
        if ('' === $path) {
            return null;
        }

        if (!str_starts_with($path, '/') && !preg_match('#^https?://#i', $path)) {
            $path = '/' . $path;
        }

        return $path;
    }
}
