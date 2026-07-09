<?php

namespace App\Controller\Admin;

use App\Entity\Portal;
use App\Entity\WatermarkAreaConfig;
use App\Entity\WatermarkPreset;
use App\Repository\WatermarkAreaConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class WatermarkAreaConfigController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly WatermarkAreaConfigRepository $watermarkRepo,
    ) {}

    #[AdminRoute('/einstellungen/wasserzeichen/bereiche', name: 'admin_watermark_areas')]
    public function index(Request $request): Response
    {
        $presets = $this->em->createQueryBuilder()
            ->select('p')
            ->from(WatermarkPreset::class, 'p')
            ->where('p.active = true')
            ->orderBy('p.name', 'ASC')
            ->getQuery()->getResult();

        /** @var Portal[] $portals */
        $portals = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Portal::class, 'p')
            ->where('p.portalKey IS NOT NULL')
            ->andWhere('p.portalKey != :empty')
            ->setParameter('empty', '')
            ->orderBy('p.name', 'ASC')
            ->getQuery()->getResult();

        // Build list of scopes: null = global, then each portal key
        $scopes = [['key' => null, 'label' => 'Global (Standard-Fallback)']];
        foreach ($portals as $portal) {
            $scopes[] = ['key' => $portal->getPortalKey(), 'label' => $portal->getName()];
        }

        // Ensure all configs exist for all scopes
        foreach ($scopes as $scope) {
            foreach (WatermarkAreaConfig::AREAS as $area) {
                $config = $this->watermarkRepo->findOneBy(['area' => $area, 'portalKey' => $scope['key']]);
                if (null === $config) {
                    $config = (new WatermarkAreaConfig())->setArea($area)->setPortalKey($scope['key']);
                    $this->em->persist($config);
                }
            }
        }
        $this->em->flush();

        if ($request->isMethod('POST')) {
            $portalKey = $request->request->get('portal_key_scope');
            $portalKey = ($portalKey === '' || $portalKey === 'null') ? null : $portalKey;

            foreach (WatermarkAreaConfig::AREAS as $area) {
                $config = $this->watermarkRepo->findOneBy(['area' => $area, 'portalKey' => $portalKey]);
                if (null === $config) {
                    continue;
                }

                $fieldKey = $area . ($portalKey ? '_' . $portalKey : '');
                $enabled  = (bool) $request->request->get('enabled_' . $fieldKey, false);
                $presetId = (int) $request->request->get('preset_' . $fieldKey, 0);
                $preset   = $presetId > 0 ? $this->em->find(WatermarkPreset::class, $presetId) : null;

                $config->setEnabled($enabled)->setPreset($preset);
            }
            $this->em->flush();
            $this->addFlash('success', 'Wasserzeichen-Konfiguration gespeichert.');

            return $this->redirectToRoute('admin_admin_watermark_areas', [
                'activeScope' => $portalKey ?? 'global',
            ]);
        }

        // Build tab data
        $tabs = [];
        foreach ($scopes as $scope) {
            $rows = [];
            foreach (WatermarkAreaConfig::AREAS as $label => $area) {
                $config = $this->watermarkRepo->findOneBy(['area' => $area, 'portalKey' => $scope['key']]);
                $rows[] = ['label' => $label, 'area' => $area, 'config' => $config];
            }
            $tabs[] = [
                'key'   => $scope['key'],
                'label' => $scope['label'],
                'rows'  => $rows,
            ];
        }

        return $this->render('admin/watermark/areas.html.twig', [
            'tabs'        => $tabs,
            'presets'     => $presets,
            'activeScope' => $request->query->get('activeScope', 'global'),
        ]);
    }
}
