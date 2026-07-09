<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Textbaustein;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class TextbausteinApiController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    #[Route('/admin/api/textbausteine', name: 'admin_api_textbausteine', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function list(): JsonResponse
    {
        $items = $this->em->getRepository(Textbaustein::class)
            ->createQueryBuilder('t')
            ->where('t.active = true')
            ->orderBy('t.sorting', 'ASC')
            ->addOrderBy('t.key', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->json(array_map(fn (Textbaustein $t) => [
            'id'     => $t->getId(),
            'key'    => $t->getKey(),
            'type'   => $t->getType(),
            'textDe' => $t->getTextDe() ?? '',
            'textEn' => $t->getTextEn() ?? '',
        ], $items));
    }
}
