<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Portal;
use App\Portals\SuperadminPortal\Service\TransmissionSupplierLogoEnricher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * BMW-Getriebe- und EGS-Wissensdatenbank. Die Daten liegen als JSON-Datei im Projekt (keine Datenbanktabelle),
 * gleicher Aufbau wie die Mercedes-Datei (siehe MercedesTransmissionController).
 */
final class BmwTransmissionController extends AbstractController
{
    private const SCHEMA_VERSION = 1;

    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        private readonly TransmissionSupplierLogoEnricher $supplierLogoEnricher,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    #[Route('/operations-api/bmw-transmissions', name: 'superadmin_portal_operations_api_bmw_transmissions', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $portal = $this->portalRuntimeResolver->requirePortalSystem($request);
        if ($portal->getPortalType() !== Portal::TYPE_SUPERADMIN_PORTAL) {
            throw new NotFoundHttpException('Superadmin operations API not available for this portal.');
        }

        $path = $this->projectDir.'/docs/bmw-getriebe.json';
        $content = is_file($path) ? file_get_contents($path) : false;
        $database = false !== $content ? json_decode($content, true) : null;

        if (!is_array($database)) {
            return $this->json(['message' => 'Die BMW-Getriebe-Datei fehlt oder ist kein gültiges JSON.'], 500);
        }

        $version = $database['meta']['frontend_schema_version'] ?? null;

        return $this->json(['data' => $this->supplierLogoEnricher->enrich($database), 'schemaVersion' => $version, 'expectedSchemaVersion' => self::SCHEMA_VERSION]);
    }
}
