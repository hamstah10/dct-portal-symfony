<?php

declare(strict_types=1);

namespace Tuningfux\InstagramBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Tuningfux\InstagramBundle\Service\InstagramClient;
use Tuningfux\InstagramBundle\Service\InstagramPublisher;
use Tuningfux\InstagramBundle\Store\InstagramAccountStoreInterface;

final class ApiController extends AbstractController
{
    public function __construct(
        private readonly InstagramAccountStoreInterface $store,
        private readonly InstagramClient $client,
        private readonly InstagramPublisher $publisher,
    ) {
    }

    public function accounts(): JsonResponse
    {
        $items = array_map(static function ($account): array {
            return [
                'id' => $account->getId(),
                'instagramUserId' => $account->getInstagramUserId(),
                'username' => $account->getUsername(),
                'accountType' => $account->getAccountType(),
                'tokenExpiresAt' => $account->getTokenExpiresAt()?->format(DATE_ATOM),
            ];
        }, $this->store->findAll());

        return $this->json($items);
    }

    public function profile(int $id): JsonResponse
    {
        $account = $this->store->findById($id);
        if ($account === null) {
            return $this->json(['ok' => false, 'error' => 'Account nicht gefunden'], 404);
        }

        $data = $this->client->get('me', [
            'fields' => 'user_id,username,account_type,profile_picture_url',
            'access_token' => $account->getAccessToken(),
        ]);

        return $this->json($data);
    }

    public function media(int $id): JsonResponse
    {
        $account = $this->store->findById($id);
        if ($account === null) {
            return $this->json(['ok' => false, 'error' => 'Account nicht gefunden'], 404);
        }

        $data = $this->client->get($account->getInstagramUserId() . '/media', [
            'fields' => 'id,caption,media_type,media_url,permalink,thumbnail_url,timestamp',
            'access_token' => $account->getAccessToken(),
        ]);

        return $this->json($data);
    }

    public function publishImage(int $id, Request $request): JsonResponse
    {
        $account = $this->store->findById($id);
        if ($account === null) {
            return $this->json(['ok' => false, 'error' => 'Account nicht gefunden'], 404);
        }

        $payload = json_decode($request->getContent(), true) ?? [];
        $imageUrl = (string) ($payload['image_url'] ?? '');
        $caption = (string) ($payload['caption'] ?? '');

        if ($imageUrl === '') {
            return $this->json(['ok' => false, 'error' => 'image_url fehlt'], 400);
        }

        return $this->json($this->publisher->publishImage($account, $imageUrl, $caption));
    }

    public function insights(int $id): JsonResponse
    {
        $account = $this->store->findById($id);
        if ($account === null) {
            return $this->json(['ok' => false, 'error' => 'Account nicht gefunden'], 404);
        }

        $data = $this->client->get($account->getInstagramUserId() . '/insights', [
            'metric' => 'views,reach,follower_count',
            'period' => 'day',
            'metric_type' => 'total_value',
            'access_token' => $account->getAccessToken(),
        ]);

        return $this->json($data);
    }
}
