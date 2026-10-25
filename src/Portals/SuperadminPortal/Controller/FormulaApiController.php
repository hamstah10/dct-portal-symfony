<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Formula;
use App\Entity\Portal;
use App\Repository\FormulaRepository;
use App\Service\Formula\FormulaEvaluator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Formeln & Logik: Formeln mit Variablen und Wenn-Dann-Regeln verwalten und testen.
 */
final class FormulaApiController extends AbstractController
{
    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        private readonly FormulaRepository $formulas,
        private readonly FormulaEvaluator $evaluator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/operations-api/formulas', name: 'superadmin_portal_formulas_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        return $this->json(['data' => array_map($this->serialize(...), $this->formulas->findAllOrdered())]);
    }

    #[Route('/operations-api/formulas', name: 'superadmin_portal_formulas_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $formula = new Formula();
        $this->apply($formula, $this->payload($request));
        $this->entityManager->persist($formula);
        $this->entityManager->flush();

        return $this->json(['data' => $this->serialize($formula)], JsonResponse::HTTP_CREATED);
    }

    #[Route('/operations-api/formulas/validate', name: 'superadmin_portal_formulas_validate', methods: ['POST'])]
    public function validate(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $payload = $this->payload($request);
        $errors = $this->evaluator->validate($this->normalizeRules($payload['rules'] ?? []), $this->normalizeVariables($payload['variables'] ?? []));

        return $this->json(['data' => ['errors' => $errors]]);
    }

    /** Testfeld: wertet die gesendeten (auch ungespeicherten) Regeln mit Beispielwerten aus. */
    #[Route('/operations-api/formulas/evaluate', name: 'superadmin_portal_formulas_evaluate', methods: ['POST'])]
    public function evaluate(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $payload = $this->payload($request);
        $values = is_array($payload['values'] ?? null) ? $payload['values'] : [];

        return $this->json(['data' => $this->evaluator->run(
            $this->normalizeRules($payload['rules'] ?? []),
            $this->normalizeVariables($payload['variables'] ?? []),
            $values,
        )]);
    }

    /** Wertet eine gespeicherte Formel über ihren Schlüssel aus, damit andere Bereiche sie nutzen können. */
    #[Route('/operations-api/formulas/{key}/evaluate', name: 'superadmin_portal_formulas_evaluate_key', requirements: ['key' => '[a-z0-9][a-z0-9._-]*'], methods: ['POST'])]
    public function evaluateByKey(string $key, Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $payload = $this->payload($request);
        $values = is_array($payload['values'] ?? null) ? $payload['values'] : [];

        $formula = $this->formulas->findOneByKey($key);
        $result = $this->evaluator->evaluateByKey($key, $values);

        return $this->json(['data' => [
            'result' => $result['result'],
            'rule' => $result['rule'],
            'error' => $result['error'],
            'name' => $formula?->getName(),
            'unit' => $formula?->getUnit(),
        ]]);
    }

    #[Route('/operations-api/formulas/{id}', name: 'superadmin_portal_formulas_update', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $formula = $this->find($id);
        $this->apply($formula, $this->payload($request));
        $this->entityManager->flush();

        return $this->json(['data' => $this->serialize($formula)]);
    }

    #[Route('/operations-api/formulas/{id}', name: 'superadmin_portal_formulas_delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function delete(int $id, Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $this->entityManager->remove($this->find($id));
        $this->entityManager->flush();

        return $this->json(['data' => ['deleted' => true]]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function apply(Formula $formula, array $payload): void
    {
        $key = strtolower(trim((string) ($payload['key'] ?? '')));
        if (1 !== preg_match('/^[a-z0-9][a-z0-9._-]{0,118}$/', $key)) {
            throw new BadRequestHttpException('Schlüssel: nur Kleinbuchstaben, Zahlen, Punkt, Unterstrich und Bindestrich.');
        }
        $existing = $this->formulas->findOneByKey($key);
        if (null !== $existing && $existing->getId() !== $formula->getId()) {
            throw new BadRequestHttpException('Dieser Schlüssel ist bereits vergeben.');
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $area = trim((string) ($payload['area'] ?? ''));
        if ('' === $name || '' === $area) {
            throw new BadRequestHttpException('Name und Bereich sind Pflicht.');
        }

        $variables = $this->normalizeVariables($payload['variables'] ?? []);
        $rules = $this->normalizeRules($payload['rules'] ?? []);
        $errors = $this->evaluator->validate($rules, $variables);
        if ([] !== $errors) {
            throw new BadRequestHttpException(implode(' ', $errors));
        }

        $description = trim((string) ($payload['description'] ?? ''));
        $unit = trim((string) ($payload['unit'] ?? ''));

        $formula
            ->setKey($key)
            ->setName($name)
            ->setArea($area)
            ->setDescription('' === $description ? null : $description)
            ->setUnit('' === $unit ? null : $unit)
            ->setVariables($variables)
            ->setRules($rules)
            ->setActive((bool) ($payload['active'] ?? true))
            ->touch();
    }

    /**
     * @return list<array{name: string, label: string, default: float|int|string|null}>
     */
    private function normalizeVariables(mixed $raw): array
    {
        $variables = [];
        foreach (is_array($raw) ? $raw : [] as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if ('' === $name) {
                continue;
            }
            if (1 !== preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
                throw new BadRequestHttpException(sprintf('Ungültiger Variablenname "%s".', $name));
            }
            $default = $item['default'] ?? null;
            $variables[] = [
                'name' => $name,
                'label' => trim((string) ($item['label'] ?? '')),
                'default' => is_numeric($default) ? $default + 0 : (is_string($default) && '' !== $default ? $default : null),
            ];
        }

        return $variables;
    }

    /**
     * @return list<array{label: string, condition: string, expression: string}>
     */
    private function normalizeRules(mixed $raw): array
    {
        $rules = [];
        foreach (is_array($raw) ? $raw : [] as $item) {
            $rules[] = [
                'label' => trim((string) ($item['label'] ?? '')),
                'condition' => trim((string) ($item['condition'] ?? '')),
                'expression' => trim((string) ($item['expression'] ?? '')),
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $payload = json_decode($request->getContent(), true);

        return is_array($payload) ? $payload : throw new BadRequestHttpException('Ungültiger Request-Body.');
    }

    private function find(int $id): Formula
    {
        return $this->formulas->find($id) ?? throw new NotFoundHttpException('Formel nicht gefunden.');
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(Formula $formula): array
    {
        return [
            'id' => $formula->getId(),
            'key' => $formula->getKey(),
            'name' => $formula->getName(),
            'area' => $formula->getArea(),
            'description' => $formula->getDescription(),
            'unit' => $formula->getUnit(),
            'variables' => $formula->getVariables(),
            'rules' => $formula->getRules(),
            'active' => $formula->isActive(),
            'updatedAt' => $formula->getUpdatedAt()->format(\DATE_ATOM),
        ];
    }

    private function assertAccess(Request $request): void
    {
        $systemType = (string) $request->attributes->get('_brand_system_type', '');
        if (SystemType::SUPERADMIN === $systemType) {
            return;
        }

        $portal = $this->portalRuntimeResolver->requirePortalSystem($request);
        if ($portal->getPortalType() !== Portal::TYPE_SUPERADMIN_PORTAL) {
            throw new NotFoundHttpException('Superadmin operations API not available for this portal.');
        }
    }
}
