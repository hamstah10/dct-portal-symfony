<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Entity\PerformanceFormula;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class PerformanceFormulaApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/operations-api/performance-formulas', name: 'superadmin_portal_performance_formulas_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $formulas = $this->entityManager->getRepository(PerformanceFormula::class)
            ->createQueryBuilder('pf')
            ->orderBy('pf.sorting', 'ASC')
            ->getQuery()
            ->getResult();

        $data = array_map(fn (PerformanceFormula $f) => [
            'id' => $f->getId(),
            'name' => $f->getName(),
            'label' => $f->getLabel(),
            'formula' => $f->getFormula(),
            'resultUnit' => $f->getResultUnit(),
            'requiredFields' => $f->getRequiredFields(),
            'sorting' => $f->getSorting(),
            'active' => $f->isActive(),
        ], $formulas);

        return $this->json(['data' => $data]);
    }

    #[Route('/operations-api/performance-formulas/{id}', name: 'superadmin_portal_performance_formulas_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $formula = $this->entityManager->getRepository(PerformanceFormula::class)->find($id);
        if (!$formula) {
            throw new NotFoundHttpException('Formula not found');
        }

        return $this->json(['data' => [
            'id' => $formula->getId(),
            'name' => $formula->getName(),
            'label' => $formula->getLabel(),
            'formula' => $formula->getFormula(),
            'resultUnit' => $formula->getResultUnit(),
            'requiredFields' => $formula->getRequiredFields(),
            'sorting' => $formula->getSorting(),
            'active' => $formula->isActive(),
        ]]);
    }

    #[Route('/operations-api/performance-formulas', name: 'superadmin_portal_performance_formulas_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            throw new BadRequestHttpException('Invalid JSON payload');
        }

        $formula = new PerformanceFormula();
        $formula->setName((string) ($payload['name'] ?? ''));
        $formula->setLabel((string) ($payload['label'] ?? ''));
        $formula->setFormula((string) ($payload['formula'] ?? ''));
        $formula->setResultUnit((string) ($payload['resultUnit'] ?? ''));
        $formula->setRequiredFields((array) ($payload['requiredFields'] ?? []));
        $formula->setSorting((int) ($payload['sorting'] ?? 0));
        $formula->setActive((bool) ($payload['active'] ?? true));

        $this->entityManager->persist($formula);
        $this->entityManager->flush();

        return $this->json(['data' => [
            'id' => $formula->getId(),
            'name' => $formula->getName(),
            'label' => $formula->getLabel(),
            'formula' => $formula->getFormula(),
            'resultUnit' => $formula->getResultUnit(),
            'requiredFields' => $formula->getRequiredFields(),
            'sorting' => $formula->getSorting(),
            'active' => $formula->isActive(),
        ]], 201);
    }

    #[Route('/operations-api/performance-formulas/{id}', name: 'superadmin_portal_performance_formulas_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $formula = $this->entityManager->getRepository(PerformanceFormula::class)->find($id);
        if (!$formula) {
            throw new NotFoundHttpException('Formula not found');
        }

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            throw new BadRequestHttpException('Invalid JSON payload');
        }

        if (isset($payload['name'])) $formula->setName((string) $payload['name']);
        if (isset($payload['label'])) $formula->setLabel((string) $payload['label']);
        if (isset($payload['formula'])) $formula->setFormula((string) $payload['formula']);
        if (isset($payload['resultUnit'])) $formula->setResultUnit((string) $payload['resultUnit']);
        if (isset($payload['requiredFields'])) $formula->setRequiredFields((array) $payload['requiredFields']);
        if (isset($payload['sorting'])) $formula->setSorting((int) $payload['sorting']);
        if (isset($payload['active'])) $formula->setActive((bool) $payload['active']);

        $this->entityManager->flush();

        return $this->json(['data' => [
            'id' => $formula->getId(),
            'name' => $formula->getName(),
            'label' => $formula->getLabel(),
            'formula' => $formula->getFormula(),
            'resultUnit' => $formula->getResultUnit(),
            'requiredFields' => $formula->getRequiredFields(),
            'sorting' => $formula->getSorting(),
            'active' => $formula->isActive(),
        ]]);
    }

    #[Route('/operations-api/performance-formulas/{id}', name: 'superadmin_portal_performance_formulas_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $formula = $this->entityManager->getRepository(PerformanceFormula::class)->find($id);
        if (!$formula) {
            throw new NotFoundHttpException('Formula not found');
        }

        $this->entityManager->remove($formula);
        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }
}
