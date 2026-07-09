<?php

declare(strict_types=1);

namespace Tuningfux\InstagramBundle\Service;

use Tuningfux\InstagramBundle\Entity\InstagramAccount;

final class InstagramPublisher
{
    public function __construct(
        private readonly InstagramClient $client,
    ) {
    }

    public function publishImage(InstagramAccount $account, string $imageUrl, string $caption = ''): array
    {
        $container = $this->client->post($account->getInstagramUserId() . '/media', [
            'image_url' => $imageUrl,
            'caption' => $caption,
            'access_token' => $account->getAccessToken(),
        ]);

        if (empty($container['id'])) {
            return [
                'ok' => false,
                'step' => 'container',
                'response' => $container,
            ];
        }

        $publish = $this->client->post($account->getInstagramUserId() . '/media_publish', [
            'creation_id' => $container['id'],
            'access_token' => $account->getAccessToken(),
        ]);

        return [
            'ok' => !empty($publish['id']),
            'container' => $container,
            'publish' => $publish,
        ];
    }
}
