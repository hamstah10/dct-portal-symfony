<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tuningfux\InstagramBundle\Service\InstagramPublisher;
use Tuningfux\InstagramBundle\Store\InstagramAccountStoreInterface;

#[Route('/admin/instagram/{id}/publish', name: 'admin_instagram_publish_form', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
class InstagramPublishController extends AbstractController
{
    public function __construct(
        private readonly InstagramAccountStoreInterface $store,
        private readonly InstagramPublisher $publisher,
    ) {
    }

    public function __invoke(int $id, Request $request): Response
    {
        $account = $this->store->findById($id);

        if (!$account) {
            throw $this->createNotFoundException('Instagram-Account nicht gefunden.');
        }

        $result = null;

        if ($request->isMethod('POST')) {
            $imageUrl = trim((string) $request->request->get('image_url'));
            $caption = trim((string) $request->request->get('caption'));

            if ($imageUrl === '') {
                $this->addFlash('danger', 'Bitte eine image_url angeben.');
            } else {
                $result = $this->publisher->publishImage($account, $imageUrl, $caption);

                if (($result['ok'] ?? false) === true) {
                    $this->addFlash('success', 'Post wurde an Instagram übergeben.');
                } else {
                    $this->addFlash('danger', 'Posting fehlgeschlagen.');
                }
            }
        }

        return $this->render('admin/instagram/publish.html.twig', [
            'account' => $account,
            'result' => $result,
        ]);
    }
}
