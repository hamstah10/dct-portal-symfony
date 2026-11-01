<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Portal;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * Liefert das Journal (config/changelog.yaml) für die Superadmin-Seite /changelog.
 */
final class ChangelogApiController extends AbstractController
{
    #[Route('/api/changelog', name: 'superadmin_portal_changelog', methods: ['GET'])]
    public function list(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        if (SystemType::SUPERADMIN !== (string) $request->attributes->get('_brand_system_type', '')) {
            $portal = $portalRuntimeResolver->requirePortalSystem($request);
            if ($portal->getPortalType() !== Portal::TYPE_SUPERADMIN_PORTAL) {
                throw new NotFoundHttpException('Not available for this portal.');
            }
        }

        $file = rtrim((string) $this->getParameter('kernel.project_dir'), '/').'/config/changelog.yaml';
        $data = is_file($file) ? Yaml::parseFile($file) : [];
        $entries = [];
        foreach (is_array($data['entries'] ?? null) ? $data['entries'] : [] as $e) {
            if (!is_array($e)) {
                continue;
            }
            $entries[] = [
                'date' => (string) ($e['date'] ?? ''),
                'version' => (string) ($e['version'] ?? ''),
                'type' => (string) ($e['type'] ?? 'change'),
                'portals' => array_values(array_map('strval', (array) ($e['portals'] ?? []))),
                'title' => (string) ($e['title'] ?? ''),
                'description' => (string) ($e['description'] ?? ''),
                'items' => array_values(array_map('strval', (array) ($e['items'] ?? []))),
            ];
        }

        return $this->json(['entries' => $entries]);
    }
}
