<?php

declare(strict_types=1);

namespace Tuningfux\InstagramBundle\Service;

use Tuningfux\InstagramBundle\Entity\InstagramAccount;
use Tuningfux\InstagramBundle\Exception\InstagramBundleException;
use Tuningfux\InstagramBundle\Store\InstagramAccountStoreInterface;

final class InstagramAuthService
{
    public function __construct(
        private readonly InstagramClient $client,
        private readonly InstagramAccountStoreInterface $store,
        private readonly string $redirectUri,
        private readonly string $appId,
    ) {
    }

    public function getAuthorizeUrl(): string
    {
        $scopes = implode(',', [
            'instagram_business_basic',
            'instagram_business_content_publish',
            'instagram_business_manage_comments',
            'instagram_business_insights',
        ]);

        return 'https://www.instagram.com/oauth/authorize'
            . '?enable_fb_login=0'
            . '&force_authentication=1'
            . '&client_id=' . urlencode($this->appId)
            . '&redirect_uri=' . urlencode($this->redirectUri)
            . '&response_type=code'
            . '&scope=' . urlencode($scopes);
    }

    public function handleCallback(string $code): InstagramAccount
    {
        $tokenData = $this->client->exchangeCodeForAccessToken($code, $this->redirectUri);

        if (empty($tokenData['access_token'])) {
            throw new InstagramBundleException('Kein Access Token empfangen.');
        }

        $shortLivedToken = (string) $tokenData['access_token'];
        $longLivedData = $this->client->exchangeForLongLivedToken($shortLivedToken);

        $accessToken = (string) ($longLivedData['access_token'] ?? $shortLivedToken);
        $expiresIn = isset($longLivedData['expires_in']) ? (int) $longLivedData['expires_in'] : null;

        $me = $this->client->get('me', [
            'fields' => 'user_id,username,account_type,profile_picture_url',
            'access_token' => $accessToken,
        ]);

        if (empty($me['user_id']) || empty($me['username'])) {
            throw new InstagramBundleException('Instagram user data konnten nicht geladen werden.');
        }

        $account = $this->store->findByInstagramUserId((string) $me['user_id']) ?? new InstagramAccount();

        $account
            ->setInstagramUserId((string) $me['user_id'])
            ->setUsername((string) $me['username'])
            ->setAccessToken($accessToken)
            ->setAccountType(isset($me['account_type']) ? (string) $me['account_type'] : null)
            ->setProfilePictureUrl(isset($me['profile_picture_url']) ? (string) $me['profile_picture_url'] : null);

        if ($expiresIn !== null) {
            $account->setTokenExpiresAt(new \DateTimeImmutable(sprintf('+%d seconds', $expiresIn)));
        }

        $this->store->save($account);

        return $account;
    }

    public function refreshToken(InstagramAccount $account): InstagramAccount
    {
        $result = $this->client->refreshLongLivedToken($account->getAccessToken());

        if (!empty($result['access_token'])) {
            $account->setAccessToken((string) $result['access_token']);
        }

        if (!empty($result['expires_in'])) {
            $account->setTokenExpiresAt(new \DateTimeImmutable(sprintf('+%d seconds', (int) $result['expires_in'])));
        }

        $this->store->save($account);

        return $account;
    }
}
