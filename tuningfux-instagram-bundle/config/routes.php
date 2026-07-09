<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Tuningfux\InstagramBundle\Controller\ApiController;
use Tuningfux\InstagramBundle\Controller\AuthController;
use Tuningfux\InstagramBundle\Controller\WebhookController;

return static function (RoutingConfigurator $routes): void {
    $routes->add('tuningfux_instagram_connect', '/instagram/connect')
        ->controller([AuthController::class, 'connect'])
        ->methods(['GET']);

    $routes->add('tuningfux_instagram_callback', '/instagram/callback')
        ->controller([AuthController::class, 'callback'])
        ->methods(['GET']);

    $routes->add('tuningfux_instagram_accounts', '/instagram/accounts')
        ->controller([ApiController::class, 'accounts'])
        ->methods(['GET']);

    $routes->add('tuningfux_instagram_profile', '/instagram/{id}/profile')
        ->controller([ApiController::class, 'profile'])
        ->methods(['GET'])
        ->requirements(['id' => '\\d+']);

    $routes->add('tuningfux_instagram_media', '/instagram/{id}/media')
        ->controller([ApiController::class, 'media'])
        ->methods(['GET'])
        ->requirements(['id' => '\\d+']);

    $routes->add('tuningfux_instagram_publish_image', '/instagram/{id}/publish-image')
        ->controller([ApiController::class, 'publishImage'])
        ->methods(['POST'])
        ->requirements(['id' => '\\d+']);

    $routes->add('tuningfux_instagram_insights', '/instagram/{id}/insights')
        ->controller([ApiController::class, 'insights'])
        ->methods(['GET'])
        ->requirements(['id' => '\\d+']);

    $routes->add('tuningfux_instagram_webhook_verify', '/instagram/webhook')
        ->controller([WebhookController::class, 'verify'])
        ->methods(['GET']);

    $routes->add('tuningfux_instagram_webhook_receive', '/instagram/webhook')
        ->controller([WebhookController::class, 'receive'])
        ->methods(['POST']);
};
