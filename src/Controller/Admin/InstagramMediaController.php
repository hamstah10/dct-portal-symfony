<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tuningfux\InstagramBundle\Service\InstagramClient;
use Tuningfux\InstagramBundle\Store\InstagramAccountStoreInterface;

#[Route('/admin/instagram/{id}/media', name: 'admin_instagram_media', methods: ['GET'], requirements: ['id' => '\\d+'])]
class InstagramMediaController extends AbstractController
{
    public function __construct(
        private readonly InstagramAccountStoreInterface $store,
        private readonly InstagramClient $client,
    ) {
    }

    public function __invoke(int $id): Response
    {
        $account = $this->store->findById($id);

        if (!$account) {
            throw $this->createNotFoundException('Instagram-Account nicht gefunden.');
        }

        $media = $this->client->get($account->getInstagramUserId().'/media', [
            'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp',
            'access_token' => $account->getAccessToken(),
        ]);

        return $this->render('admin/instagram/media.html.twig', [
            'account' => $account,
            'mediaItems' => $media['data'] ?? [],
        ]);
    }
}
