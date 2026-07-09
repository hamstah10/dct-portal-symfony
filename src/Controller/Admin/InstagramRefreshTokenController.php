<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Tuningfux\InstagramBundle\Service\InstagramClient;
use Tuningfux\InstagramBundle\Store\InstagramAccountStoreInterface;

#[Route('/admin/instagram/{id}/refresh-token', name: 'admin_instagram_refresh_token', methods: ['GET'], requirements: ['id' => '\\d+'])]
class InstagramRefreshTokenController extends AbstractController
{
    public function __construct(
        private readonly InstagramAccountStoreInterface $store,
        private readonly InstagramClient $client,
    ) {
    }

    public function __invoke(int $id): RedirectResponse
    {
        $account = $this->store->findById($id);

        if (!$account) {
            throw $this->createNotFoundException('Instagram-Account nicht gefunden.');
        }

        try {
            $result = $this->client->refreshLongLivedToken($account->getAccessToken());

            if (!empty($result['access_token'])) {
                $account->setAccessToken($result['access_token']);
            }

            if (!empty($result['expires_in'])) {
                $account->setTokenExpiresAt(new \DateTimeImmutable(sprintf('+%d seconds', (int) $result['expires_in'])));
            }

            $this->store->save($account);
            $this->addFlash('success', 'Token erfolgreich aktualisiert.');
        } catch (\Throwable $e) {
            $this->addFlash('danger', 'Token-Refresh fehlgeschlagen: '.$e->getMessage());
        }

        return $this->redirectToRoute('admin_instagram_index');
    }
}
