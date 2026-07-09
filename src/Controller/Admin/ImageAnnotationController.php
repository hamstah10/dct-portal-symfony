<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\MediaAsset;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ImageAnnotationController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    #[AdminRoute('/media-asset/{id}/annotate', name: 'admin_image_annotate')]
    public function editor(int $id, Request $request): Response
    {
        $asset = $this->em->find(MediaAsset::class, $id);
        if (null === $asset) {
            throw $this->createNotFoundException('MediaAsset nicht gefunden.');
        }

        $backUrl = $request->query->get('back', '');

        return $this->render('admin/annotation/editor.html.twig', [
            'asset'   => $asset,
            'backUrl' => $backUrl,
        ]);
    }

    #[Route('/admin/api/media-asset/{id}/annotations', name: 'admin_api_media_asset_annotations_get', methods: ['GET'])]
    public function getAnnotations(int $id): JsonResponse
    {
        $asset = $this->em->find(MediaAsset::class, $id);
        if (null === $asset) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['annotations' => $asset->getAnnotations()]);
    }

    #[Route('/admin/api/media-asset/{id}/annotations', name: 'admin_api_media_asset_annotations_save', methods: ['POST'])]
    public function saveAnnotations(int $id, Request $request): JsonResponse
    {
        $asset = $this->em->find(MediaAsset::class, $id);
        if (null === $asset) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data) || !array_key_exists('annotations', $data)) {
            return $this->json(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        $annotations = is_array($data['annotations']) ? $data['annotations'] : [];
        $asset->setAnnotations($annotations ?: null);
        $this->em->flush();

        return $this->json(['ok' => true, 'count' => count($asset->getAnnotations())]);
    }
}
