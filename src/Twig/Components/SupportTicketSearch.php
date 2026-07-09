<?php

namespace App\Twig\Components;

use App\Entity\SupportTicket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('support_ticket_search', template: 'components/support_ticket_search.html.twig')]
final class SupportTicketSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp]
    public int $limit = 40;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return array<int, array{
     *   url:string,
     *   label:string,
     *   subLabel:?string,
     *   status:string,
     *   statusLabel:string,
     *   statusTone:string,
     *   createdAt:?string
     * }>
     */
    public function getResults(): array
    {
        $query = trim($this->query);
        $length = function_exists('mb_strlen') ? mb_strlen($query) : strlen($query);
        if ($length < 2) {
            return [];
        }

        $normalized = function_exists('mb_strtolower') ? mb_strtolower($query) : strtolower($query);
        $term = '%' . $normalized . '%';
        $ticketId = $this->extractTicketId($normalized);

        $qb = $this->entityManager->createQueryBuilder()
            ->select('t', 'k', 'f')
            ->from(SupportTicket::class, 't')
            ->leftJoin('t.kunde', 'k')
            ->leftJoin('t.firma', 'f')
            ->orderBy('t.lastMessageAt', 'DESC')
            ->addOrderBy('t.updatedAt', 'DESC')
            ->setMaxResults(max(1, $this->limit));

        $expr = $qb->expr()->orX(
            $qb->expr()->like('LOWER(COALESCE(t.subject, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(f.name, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(f.kuerzel, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.vorname, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.nachname, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.kundennummer, \'\'))', ':term')
        );

        if (null !== $ticketId) {
            $expr->add($qb->expr()->eq('t.id', ':ticketId'));
            $qb->setParameter('ticketId', $ticketId);
        }

        $items = $qb
            ->andWhere($expr)
            ->setParameter('term', $term)
            ->getQuery()
            ->getResult();

        $results = [];
        $statusLabels = SupportTicket::getStatusChoices();
        $statusLabelsByValue = array_flip($statusLabels);

        foreach ($items as $ticket) {
            if (!$ticket instanceof SupportTicket) {
                continue;
            }

            $status = $ticket->getStatus();
            $results[] = [
                'url' => $this->urlGenerator->generate('admin_support_tickets', [
                    'ticket' => $ticket->getId(),
                    'status' => 'all',
                    'firma' => 0,
                ]),
                'label' => $this->buildLabel($ticket),
                'subLabel' => $this->buildSubLabel($ticket),
                'status' => $status,
                'statusLabel' => $statusLabelsByValue[$status] ?? $status,
                'statusTone' => $this->statusTone($status),
                'createdAt' => $this->formatDate($ticket->getLastMessageAt() ?? $ticket->getCreatedAt()),
            ];
        }

        return $results;
    }

    private function extractTicketId(string $query): ?int
    {
        if ('' === $query) {
            return null;
        }

        if (preg_match('/^(t-?)?(\d+)$/i', $query, $matches)) {
            return (int) $matches[2];
        }

        return null;
    }

    private function buildLabel(SupportTicket $ticket): string
    {
        $subject = trim($ticket->getSubject());
        $label = $ticket->getTicketNumber();
        if ('' !== $subject) {
            $label .= ' · ' . $subject;
        }

        return $label;
    }

    private function buildSubLabel(SupportTicket $ticket): ?string
    {
        $parts = [];

        $firmaName = trim((string) ($ticket->getFirma()?->getName() ?? ''));
        if ('' !== $firmaName) {
            $parts[] = $firmaName;
        }

        $kunde = $ticket->getKunde();
        if ($kunde) {
            $kundeName = trim(sprintf('%s %s', (string) ($kunde->getVorname() ?? ''), (string) ($kunde->getNachname() ?? '')));
            if ('' !== $kundeName) {
                $parts[] = $kundeName;
            }
        }

        return $parts ? implode(' | ', $parts) : null;
    }

    private function statusTone(string $status): string
    {
        return match ($status) {
            SupportTicket::STATUS_OPEN => 'info',
            SupportTicket::STATUS_IN_PROGRESS => 'warning',
            SupportTicket::STATUS_CLOSED => 'secondary',
            default => 'secondary',
        };
    }

    private function formatDate(?\DateTimeImmutable $date): ?string
    {
        if (!$date) {
            return null;
        }

        return $date->format('d.m.Y H:i');
    }
}
