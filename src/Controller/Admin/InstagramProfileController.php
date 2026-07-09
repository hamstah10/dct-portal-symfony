<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tuningfux\InstagramBundle\Service\InstagramClient;
use Tuningfux\InstagramBundle\Store\InstagramAccountStoreInterface;

#[Route('/admin/instagram/{id}/profile', name: 'admin_instagram_profile', methods: ['GET'], requirements: ['id' => '\\d+'])]
class InstagramProfileController extends AbstractController
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

        $profile = $this->client->get('me', [
            'fields' => 'user_id,username,account_type,profile_picture_url',
            'access_token' => $account->getAccessToken(),
        ]);

        return $this->render('admin/instagram/profile.html.twig', [
            'account' => $account,
            'profile' => $profile,
        ]);
    }
}
