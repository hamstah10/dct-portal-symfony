<?php

namespace App\Twig\Components;

use App\Controller\Admin\EcuCrudController;
use App\Entity\Ecu;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('ecu_search', template: 'components/ecu_search.html.twig')]
final class EcuSearch
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
     *   subLabel:?string
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

        $items = $this->entityManager->createQueryBuilder()
            ->select('e', 'm')
            ->from(Ecu::class, 'e')
            ->leftJoin('e.manufacturer', 'm')
            ->andWhere('LOWER(e.name) LIKE :term OR LOWER(COALESCE(e.type, \'\')) LIKE :term OR LOWER(COALESCE(e.controlUnitKind, \'\')) LIKE :term OR LOWER(COALESCE(m.name, \'\')) LIKE :term')
            ->setParameter('term', $term)
            ->addOrderBy('e.name', 'ASC')
            ->setMaxResults(max(1, $this->limit))
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($items as $ecu) {
            if (!$ecu instanceof Ecu) {
                continue;
            }

            $label = trim($ecu->getName());
            if ('' === $label) {
                $label = 'ECU #' . (string) ($ecu->getId() ?? '');
            }

            $subParts = [];
            $manufacturer = $ecu->getManufacturer()?->getName();
            if (null !== $manufacturer && '' !== trim($manufacturer)) {
                $subParts[] = $manufacturer;
            }
            $type = $ecu->getType();
            if (null !== $type && '' !== trim($type)) {
                $subParts[] = $type;
            }
            $controlUnitKind = $ecu->getControlUnitKind();
            if (null !== $controlUnitKind && '' !== trim($controlUnitKind)) {
                $subParts[] = $controlUnitKind;
            }

            $results[] = [
                'url' => $this->adminUrlGenerator
                    ->unsetAll()
                    ->setController(EcuCrudController::class)
                    ->setAction(Action::DETAIL)
                    ->setEntityId($ecu->getId())
                    ->generateUrl(),
                'label' => $label,
                'subLabel' => $subParts ? implode(' · ', $subParts) : null,
            ];
        }

        return $results;
    }
}
