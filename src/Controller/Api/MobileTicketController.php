<?php

namespace App\Controller\Api;

use App\Entity\SupportTicket;
use App\Entity\SupportTicketMessage;
use App\Entity\User;
use App\Repository\SupportTicketRepository;
use App\Service\Api\MobileApiAuthService;
use App\Service\Api\MobileApiPayloadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/mobile/tickets')]
final class MobileTicketController extends AbstractController
{
    use ApiControllerTrait;
    use MobileAuthTrait;

    public function __construct(
        private readonly SupportTicketRepository $ticketRepository,
        private readonly MobileApiAuthService $authService,
        private readonly MobileApiPayloadService $payloadService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'api_mobile_tickets_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $user = $this->requireMobileUser($request, $this->authService);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $status = trim((string) $request->query->get('status', ''));
        $search = trim((string) $request->query->get('search', ''));
        $limit = max(1, min(100, (int) $request->query->get('limit', 20)));
        $offset = max(0, (int) $request->query->get('offset', 0));

        $qb = $this->ticketRepository->createQueryBuilder('t');

        if ('' !== $status) {
            $qb->andWhere('t.status = :status')->setParameter('status', $status);
        }

        if ('' !== $search) {
            $qb->andWhere('LOWER(t.subject) LIKE :search')
                ->setParameter('search', '%' . mb_strtolower($search) . '%');
        }

        $total = (int) (clone $qb)->select('COUNT(t.id)')->getQuery()->getSingleScalarResult();

        $tickets = $qb->orderBy('t.updatedAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'tickets' => array_map(fn (SupportTicket $ticket): array => $this->payloadService->normalizeTicket($ticket), $tickets),
            'total' => $total,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'api_mobile_tickets_show', methods: ['GET'])]
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $this->requireMobileUser($request, $this->authService);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $ticket = $this->ticketRepository->find($id);
        if (!$ticket instanceof SupportTicket) {
            return $this->errorResponse('Ticket nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->payloadService->normalizeTicketDetail($ticket));
    }

    #[Route('/{id<\d+>}/reply', name: 'api_mobile_tickets_reply', methods: ['POST'])]
    public function reply(Request $request, int $id): JsonResponse
    {
        $user = $this->requireMobileUser($request, $this->authService);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $ticket = $this->ticketRepository->find($id);
        if (!$ticket instanceof SupportTicket) {
            return $this->errorResponse('Ticket nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $messageText = trim((string) ($payload['message'] ?? ''));
        if ('' === $messageText) {
            return $this->errorResponse('message ist erforderlich.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $message = (new SupportTicketMessage())
            ->setTicket($ticket)
            ->setKunde($ticket->getKunde())
            ->setSenderType(SupportTicketMessage::SENDER_ADMIN)
            ->setSenderName($this->payloadService->composeUserName($user))
            ->setMessage($messageText);

        $ticket->addMessage($message);
        $ticket->registerNewMessage($messageText, $message->getCreatedAt() ?? new \DateTimeImmutable());

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return new JsonResponse($this->payloadService->normalizeTicketMessage($message));
    }

    #[Route('/{id<\d+>}/status', name: 'api_mobile_tickets_status', methods: ['PATCH'])]
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $user = $this->requireMobileUser($request, $this->authService);
        if (!$user instanceof User) {
            return $this->errorResponse('Access-Token ist ungueltig oder abgelaufen.', Response::HTTP_UNAUTHORIZED);
        }

        $ticket = $this->ticketRepository->find($id);
        if (!$ticket instanceof SupportTicket) {
            return $this->errorResponse('Ticket nicht gefunden.', Response::HTTP_NOT_FOUND);
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $status = trim((string) ($payload['status'] ?? ''));
        if (!\in_array($status, SupportTicket::getStatusChoices(), true)) {
            return $this->errorResponse('Ungueltiger Status.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $ticket->setStatus($status);
        $ticket->setUpdatedAt(new \DateTimeImmutable());
        if (SupportTicket::STATUS_CLOSED === $ticket->getStatus()) {
            $ticket->setClosedAt(new \DateTimeImmutable());
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Status aktualisiert.',
            'ticket' => $this->payloadService->normalizeTicket($ticket),
        ]);
    }
}
