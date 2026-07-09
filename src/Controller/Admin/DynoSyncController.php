<?php

namespace App\Controller\Admin;

use App\Service\DynoRemoteSyncService;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/dyno')]
final class DynoSyncController extends AbstractController
{
    public function __construct(
        private readonly DynoRemoteSyncService $syncService,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    #[Route('/sync/{type}', name: 'admin_dyno_sync', methods: ['GET'])]
    public function sync(string $type, Request $request): RedirectResponse
    {
        $type = strtolower(trim($type));
        $controller = match ($type) {
            DynoRemoteSyncService::TYPE_VEHICLES => DynoVehicleCrudController::class,
            DynoRemoteSyncService::TYPE_CUSTOMERS => DynoCustomerCrudController::class,
            DynoRemoteSyncService::TYPE_TRIPS => DynoTripCrudController::class,
            DynoRemoteSyncService::TYPE_SPRINTS => DynoSprintCrudController::class,
            DynoRemoteSyncService::TYPE_DTCS => DynoDtcCrudController::class,
            default => null,
        };

        if (null === $controller) {
            $this->addFlash('danger', 'Unbekannter Sync-Typ.');

            return $this->redirect($this->adminUrlGenerator->setDashboard(DashboardController::class)->generateUrl());
        }

        try {
            $count = $this->syncService->sync($type);
            $this->addFlash('success', sprintf('Dyno-Daten aktualisiert (%d Eintraege).', $count));
        } catch (\Throwable $exception) {
            $this->addFlash('danger', 'Sync fehlgeschlagen: ' . $exception->getMessage());
        }

        $url = $this->adminUrlGenerator
            ->setController($controller)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }
}
