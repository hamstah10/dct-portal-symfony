<?php

namespace App\Controller\Admin;

use App\Entity\Kunde;
use App\Repository\KundeRepository;
use App\Service\Brevo\BrevoApiException;
use App\Service\Brevo\BrevoService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class BrevoNewsletterController extends AbstractController
{
    public function __construct(
        private readonly BrevoService $brevoService,
        private readonly KundeRepository $kundeRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[AdminRoute(path: '/support/newsletter/brevo/lists', name: 'brevo_newsletter_lists', options: ['methods' => ['GET']])]
    public function lists(): JsonResponse
    {
        try {
            $data = $this->brevoService->lists()->list(['limit' => 50, 'offset' => 0]);

            return $this->json(['success' => true, 'data' => $data]);
        } catch (BrevoApiException $exception) {
            return $this->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'status' => $exception->getStatusCode(),
                'details' => $exception->getResponseBody(),
                'url' => $exception->getRequestUrl(),
            ], 500);
        }
    }

    #[AdminRoute(path: '/support/newsletter/brevo/stats', name: 'brevo_newsletter_stats', options: ['methods' => ['GET']])]
    public function stats(): JsonResponse
    {
        $total = $this->kundeRepository->count([]);
        $newsletter = (int) $this->kundeRepository->createQueryBuilder('k')
            ->select('COUNT(k.id)')
            ->andWhere('k.newsletter = :newsletter')
            ->setParameter('newsletter', true)
            ->getQuery()
            ->getSingleScalarResult();

        $withEmail = (int) $this->kundeRepository->createQueryBuilder('k')
            ->select('COUNT(k.id)')
            ->andWhere('k.newsletter = :newsletter')
            ->andWhere('k.email IS NOT NULL')
            ->andWhere('k.email <> :empty')
            ->setParameter('newsletter', true)
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'newsletter' => $newsletter,
                'withEmail' => $withEmail,
            ],
        ]);
    }

    #[AdminRoute(path: '/support/newsletter/brevo/sync', name: 'brevo_newsletter_sync', options: ['methods' => ['POST']])]
    public function sync(Request $request): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            $payload = $request->request->all();
        }

        $listId = 10;

        $limit = (int) ($payload['limit'] ?? 0);
        if ($limit < 0) {
            $limit = 0;
        }

        $queryBuilder = $this->kundeRepository->createQueryBuilder('k')
            ->andWhere('k.newsletter = :newsletter')
            ->setParameter('newsletter', true)
            ->orderBy('k.id', 'ASC');

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        $kunden = $queryBuilder->getQuery()->getResult();

        $result = [
            'total' => count($kunden),
            'synced' => 0,
            'skipped' => 0,
            'limit' => $limit > 0 ? $limit : null,
            'preview' => [],
            'errors' => [],
        ];

        $syncedAt = new \DateTimeImmutable();
        foreach ($kunden as $kunde) {
            if (!$kunde instanceof Kunde) {
                continue;
            }

            $email = trim((string) $kunde->getEmail());
            if ('' === $email) {
                $result['skipped']++;
                continue;
            }

            if (count($result['preview']) < 10) {
                $result['preview'][] = $email;
            }

            $contactPayload = [
                'email' => $email,
                'updateEnabled' => true,
                'listIds' => [$listId],
            ];

            $attributes = $this->buildContactAttributes($kunde);
            if ([] !== $attributes) {
                $contactPayload['attributes'] = $attributes;
            }

            try {
                $this->brevoService->contacts()->create($contactPayload);
                $kunde->setBrevoSyncedAt($syncedAt);
                $result['synced']++;
            } catch (BrevoApiException $exception) {
                $result['errors'][] = [
                    'email' => $email,
                    'status' => $exception->getStatusCode(),
                    'message' => $exception->getMessage(),
                    'details' => $exception->getResponseBody(),
                ];

                if (429 === $exception->getStatusCode()) {
                    break;
                }
            }
        }

        if ($result['synced'] > 0) {
            $this->entityManager->flush();
        }

        return $this->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    #[AdminRoute(path: '/support/newsletter/brevo/synced', name: 'brevo_newsletter_synced', options: ['methods' => ['GET']])]
    public function synced(Request $request): JsonResponse
    {
        $limit = (int) $request->query->get('limit', 0);
        if ($limit < 0) {
            $limit = 0;
        }

        $queryBuilder = $this->kundeRepository->createQueryBuilder('k')
            ->andWhere('k.brevoSyncedAt IS NOT NULL')
            ->orderBy('k.brevoSyncedAt', 'DESC');

        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }

        $kunden = $queryBuilder->getQuery()->getResult();

        $items = [];
        foreach ($kunden as $kunde) {
            if (!$kunde instanceof Kunde) {
                continue;
            }

            $nameParts = array_filter([
                trim((string) $kunde->getVorname()),
                trim((string) $kunde->getNachname()),
            ]);

            $items[] = [
                'id' => $kunde->getId(),
                'name' => '' !== trim((string) $kunde->getFirmenname())
                    ? (string) $kunde->getFirmenname()
                    : implode(' ', $nameParts),
                'email' => $kunde->getEmail(),
                'syncedAt' => $kunde->getBrevoSyncedAt()?->format('d.m.Y H:i'),
            ];
        }

        return $this->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'total' => count($items),
            ],
        ]);
    }

    #[AdminRoute(path: '/support/newsletter/brevo/clear-synced', name: 'brevo_newsletter_clear_synced', options: ['methods' => ['POST']])]
    public function clearSynced(): JsonResponse
    {
        $affected = $this->entityManager->createQueryBuilder()
            ->update(Kunde::class, 'k')
            ->set('k.brevoSyncedAt', ':value')
            ->where('k.brevoSyncedAt IS NOT NULL')
            ->setParameter('value', null)
            ->getQuery()
            ->execute();

        return $this->json([
            'success' => true,
            'data' => [
                'cleared' => (int) $affected,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildContactAttributes(Kunde $kunde): array
    {
        $attributes = [];

        if (null !== $kunde->getId()) {
            $attributes['ext_id'] = (string) $kunde->getId();
        }

        $firstName = trim((string) $kunde->getVorname());
        if ('' !== $firstName) {
            $attributes['FIRSTNAME'] = $firstName;
        }

        $lastName = trim((string) $kunde->getNachname());
        if ('' !== $lastName) {
            $attributes['LASTNAME'] = $lastName;
        }

        $company = trim((string) $kunde->getFirmenname());
        if ('' !== $company) {
            $attributes['COMPANY_NAME'] = $company;
        }

        $phone = trim((string) $kunde->getTelefon());
        if ('' !== $phone) {
            $attributes['PHONE'] = $phone;
        }

        return $attributes;
    }
}
