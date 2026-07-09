<?php

namespace App\Twig\Components;

use App\Controller\Admin\KundeCrudController;
use App\Entity\Kunde;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('kunde_search', template: 'components/kunde_search.html.twig')]
final class KundeSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    #[LiveProp]
    public int $limit = 40;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    /**
     * @return array<int, array{
     *   url:string,
     *   label:string,
     *   subLabel:?string,
     *   badge:?string,
     *   badgeTone:?string,
     *   meta:?string
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
        $kundeId = $this->extractKundeId($normalized);

        $qb = $this->entityManager->createQueryBuilder()
            ->select('k', 'f')
            ->from(Kunde::class, 'k')
            ->leftJoin('k.firma', 'f')
            ->addOrderBy('k.nachname', 'ASC')
            ->addOrderBy('k.vorname', 'ASC')
            ->setMaxResults(max(1, $this->limit));

        $expr = $qb->expr()->orX(
            $qb->expr()->like('LOWER(COALESCE(k.kundennummer, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.vorname, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.nachname, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.email, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(k.firmenname, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(f.name, \'\'))', ':term'),
            $qb->expr()->like('LOWER(COALESCE(f.kuerzel, \'\'))', ':term')
        );

        if (null !== $kundeId) {
            $expr->add($qb->expr()->eq('k.id', ':kundeId'));
            $qb->setParameter('kundeId', $kundeId);
        }

        $items = $qb
            ->andWhere($expr)
            ->setParameter('term', $term)
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($items as $kunde) {
            if (!$kunde instanceof Kunde) {
                continue;
            }

            $badgeData = $this->buildBadge($kunde);

            $results[] = [
                'url' => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(KundeCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($kunde->getId())
                    ->generateUrl(),
                'label' => $this->buildLabel($kunde),
                'subLabel' => $this->buildSubLabel($kunde),
                'badge' => $badgeData['label'] ?? null,
                'badgeTone' => $badgeData['tone'] ?? null,
                'meta' => $kunde->getCreatedAt()?->format('d.m.Y'),
            ];
        }

        return $results;
    }

    private function extractKundeId(string $query): ?int
    {
        if ('' === $query) {
            return null;
        }

        if (preg_match('/^(k-?)?(\d+)$/i', $query, $matches)) {
            return (int) $matches[2];
        }

        return null;
    }

    private function buildLabel(Kunde $kunde): string
    {
        $parts = [];

        $kundennummer = trim((string) ($kunde->getKundennummer() ?? ''));
        if ('' !== $kundennummer) {
            $parts[] = $kundennummer;
        }

        $name = trim(sprintf('%s %s', (string) ($kunde->getVorname() ?? ''), (string) ($kunde->getNachname() ?? '')));
        if ('' !== $name) {
            $parts[] = $name;
        } else {
            $firmaDisplay = trim((string) ($kunde->getFirmenname() ?? ''));
            if ('' !== $firmaDisplay) {
                $parts[] = $firmaDisplay;
            }
        }

        if (!$parts) {
            $parts[] = 'Kunde #' . (string) ($kunde->getId() ?? '');
        }

        return implode(' · ', $parts);
    }

    private function buildSubLabel(Kunde $kunde): ?string
    {
        $parts = [];

        $email = trim((string) ($kunde->getEmail() ?? ''));
        if ('' !== $email) {
            $parts[] = $email;
        }

        $firma = $kunde->getFirma();
        if ($firma) {
            $firmaName = trim((string) ($firma->getName() ?? ''));
            if ('' !== $firmaName) {
                $parts[] = $firmaName;
            }
        }

        return $parts ? implode(' | ', $parts) : null;
    }

    /**
     * @return array{label?:string,tone?:string}
     */
    private function buildBadge(Kunde $kunde): array
    {
        $firma = $kunde->getFirma();
        if (!$firma) {
            return [];
        }

        $label = trim((string) ($firma->getKuerzel() ?? ''));
        if ('' === $label) {
            $label = trim((string) ($firma->getName() ?? ''));
        }

        if ('' === $label) {
            return [];
        }

        $tone = 'secondary';
        $upper = strtoupper($label);
        if ('TFD' === $upper) {
            $tone = 'danger';
        } elseif ('CPT' === $upper) {
            $tone = 'dark';
        }

        return ['label' => $label, 'tone' => $tone];
    }
}
