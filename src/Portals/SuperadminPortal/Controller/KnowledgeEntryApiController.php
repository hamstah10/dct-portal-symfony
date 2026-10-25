<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\KnowledgeEntry;
use App\Entity\Portal;
use App\Repository\KnowledgeEntryRepository;
use App\Service\Ai\KnowledgeSearchTool;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Wissensdatenbank: Fragen und Entscheidungen aus der Planung des KI-Wissens.
 */
final class KnowledgeEntryApiController extends AbstractController
{
    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        private readonly KnowledgeEntryRepository $entries,
        private readonly EntityManagerInterface $entityManager,
        private readonly KnowledgeSearchTool $brandKnowledgeSearch,
    ) {
    }

    #[Route('/operations-api/knowledge', name: 'superadmin_portal_knowledge_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $items = array_map($this->serialize(...), $this->entries->findAllOrdered());

        return $this->json(['data' => $items]);
    }

    #[Route('/operations-api/brand-knowledge/search', name: 'superadmin_portal_brand_knowledge_search', methods: ['GET'])]
    public function searchBrandKnowledge(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $query = trim((string) $request->query->get('q', ''));
        if ('' === $query) {
            return $this->json(['data' => []]);
        }

        $result = $this->brandKnowledgeSearch->search($query);
        if (!$result['found']) {
            return $this->json(['data' => [], 'message' => $result['passages']]);
        }

        $items = [];
        foreach (preg_split('/\n\n(?=\[\d+\] )/', $result['passages']) ?: [] as $part) {
            if (1 !== preg_match('/^\[\d+\] ([^\n]*)\n(.*)$/s', $part, $matches)) {
                continue;
            }
            $items[] = ['source' => $matches[1], 'text' => $matches[2]];
        }

        return $this->json(['data' => $items]);
    }

    #[Route('/operations-api/knowledge/{code}', name: 'superadmin_portal_knowledge_update', requirements: ['code' => '[A-Z][0-9]{1,3}'], methods: ['PUT'])]
    public function update(string $code, Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $entry = $this->entries->findOneBy(['code' => $code]);
        if (null === $entry) {
            throw new NotFoundHttpException('Eintrag nicht gefunden.');
        }

        $payload = json_decode((string) $request->getContent(), true);
        if (!is_array($payload)) {
            throw new BadRequestHttpException('Ungültige Anfrage.');
        }

        if (array_key_exists('status', $payload)) {
            if (!in_array($payload['status'], KnowledgeEntry::STATUSES, true)) {
                throw new BadRequestHttpException('Unbekannter Status.');
            }
            $entry->setStatus($payload['status']);
        }

        if (array_key_exists('answer', $payload)) {
            $answer = array_values(array_filter(array_map(
                static fn (mixed $line): string => trim((string) $line),
                is_array($payload['answer']) ? $payload['answer'] : [],
            ), static fn (string $line): bool => '' !== $line));
            $entry->setAnswer([] === $answer ? null : $answer);
            if (null !== $entry->getAnswer() && null === $entry->getAnswerLabel()) {
                $entry->setAnswerLabel('Antwort des Nutzers');
            }
        }

        if (array_key_exists('note', $payload)) {
            $note = trim((string) ($payload['note'] ?? ''));
            $entry->setNote('' === $note ? null : $note);
        }

        $entry->touch();
        $this->entityManager->flush();

        return $this->json(['data' => $this->serialize($entry)]);
    }

    /**
     * @return array{code: string, group: string, status: string, question: string, answerLabel: ?string, answer: ?list<string>, listItems: ?list<string>, note: ?string, updatedAt: string}
     */
    private function serialize(KnowledgeEntry $entry): array
    {
        return [
            'code' => $entry->getCode(),
            'group' => $entry->getGroupTitle(),
            'status' => $entry->getStatus(),
            'question' => $entry->getQuestion(),
            'answerLabel' => $entry->getAnswerLabel(),
            'answer' => $entry->getAnswer(),
            'listItems' => $entry->getListItems(),
            'note' => $entry->getNote(),
            'updatedAt' => $entry->getUpdatedAt()->format(\DateTimeInterface::ATOM),
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
