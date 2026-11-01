<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Portal;
use App\Entity\SuperadminSetting;
use App\Repository\SuperadminSettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Speichert das editierbare Bento-Layout der Superadmin-Startseite.
 */
final class LandingBentoApiController extends AbstractController
{
    private const SETTING_KEY = 'landing.bento';
    private const VARIANTS = ['hero', 'dark', 'light'];
    private const MAX_TILES = 40;

    public function __construct(
        private readonly SuperadminSettingRepository $settingRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/api/landing-bento', name: 'superadmin_portal_landing_bento_get', methods: ['GET'])]
    public function get(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertSuperadminPortal($request, $portalRuntimeResolver);

        $setting = $this->settingRepository->findOneBy(['settingKey' => self::SETTING_KEY]);
        $value = $setting instanceof SuperadminSetting ? $setting->getSettingValue() : null;

        return $this->json(['tiles' => is_array($value) ? $value : null]);
    }

    #[Route('/api/landing-bento', name: 'superadmin_portal_landing_bento_put', methods: ['PUT'])]
    public function put(Request $request, PortalRuntimeResolver $portalRuntimeResolver): JsonResponse
    {
        $this->assertSuperadminPortal($request, $portalRuntimeResolver);

        $body = json_decode($request->getContent(), true);
        $setting = $this->settingRepository->findOneBy(['settingKey' => self::SETTING_KEY]);

        // tiles = null → Standard-Layout wiederherstellen
        if (is_array($body) && array_key_exists('tiles', $body) && null === $body['tiles']) {
            if ($setting instanceof SuperadminSetting) {
                $this->entityManager->remove($setting);
                $this->entityManager->flush();
            }

            return $this->json(['tiles' => null]);
        }

        if (!is_array($body) || !is_array($body['tiles'] ?? null)) {
            return $this->json(['message' => 'Ungültige Daten.'], 400);
        }

        $tiles = [];
        foreach (array_slice($body['tiles'], 0, self::MAX_TILES) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $title = trim((string) ($item['title'] ?? ''));
            if ('' === $title) {
                continue;
            }
            $variant = (string) ($item['variant'] ?? 'light');
            $tiles[] = [
                'id' => substr(preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($item['id'] ?? '')) ?: bin2hex(random_bytes(4)), 0, 40),
                'title' => mb_substr($title, 0, 60),
                'desc' => mb_substr(trim((string) ($item['desc'] ?? '')), 0, 300),
                'to' => mb_substr(trim((string) ($item['to'] ?? '/')), 0, 300),
                'icon' => substr(preg_replace('/[^a-zA-Z0-9]/', '', (string) ($item['icon'] ?? 'LayoutGrid')), 0, 40),
                'colSpan' => min(2, max(1, (int) ($item['colSpan'] ?? 1))),
                'rowSpan' => min(2, max(1, (int) ($item['rowSpan'] ?? 1))),
                'variant' => in_array($variant, self::VARIANTS, true) ? $variant : 'light',
                'visible' => (bool) ($item['visible'] ?? true),
            ];
        }

        if (!$setting instanceof SuperadminSetting) {
            $setting = (new SuperadminSetting())->setSettingKey(self::SETTING_KEY);
            $this->entityManager->persist($setting);
        }
        $setting->setSettingValue($tiles);
        $this->entityManager->flush();

        return $this->json(['tiles' => $tiles]);
    }

    private function assertSuperadminPortal(Request $request, PortalRuntimeResolver $portalRuntimeResolver): void
    {
        if (SystemType::SUPERADMIN === (string) $request->attributes->get('_brand_system_type', '')) {
            return;
        }

        $portal = $portalRuntimeResolver->requirePortalSystem($request);
        if ($portal->getPortalType() !== Portal::TYPE_SUPERADMIN_PORTAL) {
            throw new NotFoundHttpException('Not available for this portal.');
        }
    }
}
