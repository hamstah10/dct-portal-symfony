<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Entity\WatermarkAreaConfig;
use App\Repository\WatermarkAreaConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/watermark')]
final class WatermarkApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly WatermarkAreaConfigRepository $watermarkRepo,
    ) {}

    #[Route('/config/{area}', name: 'superadmin_portal_api_watermark_config', methods: ['GET'])]
    public function config(string $area, Request $request): JsonResponse
    {
        if (!array_key_exists($area, array_flip(WatermarkAreaConfig::AREAS))) {
            return $this->json(['enabled' => false]);
        }

        $portalKey = $request->query->get('portalKey');
        $config = $this->watermarkRepo->findForPortal($area, $portalKey);

        if (null === $config || !$config->isEnabled() || null === $config->getPreset()) {
            return $this->json(['enabled' => false]);
        }

        $preset = $config->getPreset();
        if (!$preset->isActive()) {
            return $this->json(['enabled' => false]);
        }

        return $this->json([
            'enabled' => true,
            'preset'  => $preset->toArray(),
        ]);
    }
}
