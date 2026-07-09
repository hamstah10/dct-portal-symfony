<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Tuningfux\InstagramBundle\Command\InstagramRefreshTokensCommand;
use Tuningfux\InstagramBundle\Controller\ApiController;
use Tuningfux\InstagramBundle\Controller\AuthController;
use Tuningfux\InstagramBundle\Controller\WebhookController;
use Tuningfux\InstagramBundle\Repository\InstagramAccountRepository;
use Tuningfux\InstagramBundle\Service\InstagramAuthService;
use Tuningfux\InstagramBundle\Service\InstagramClient;
use Tuningfux\InstagramBundle\Service\InstagramPublisher;
use Tuningfux\InstagramBundle\Store\DoctrineInstagramAccountStore;
use Tuningfux\InstagramBundle\Store\InstagramAccountStoreInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(InstagramClient::class)
        ->arg('$appId', '%tuningfux_instagram.app_id%')
        ->arg('$appSecret', '%tuningfux_instagram.app_secret%')
        ->arg('$graphVersion', '%tuningfux_instagram.graph_version%');

    $services->set(InstagramAuthService::class)
        ->arg('$redirectUri', '%tuningfux_instagram.redirect_uri%')
        ->arg('$appId', '%tuningfux_instagram.app_id%');

    $services->set(InstagramPublisher::class);

    $services->set(InstagramAccountRepository::class)
        ->tag('doctrine.repository_service');

    $services->set(DoctrineInstagramAccountStore::class);
    $services->alias(InstagramAccountStoreInterface::class, DoctrineInstagramAccountStore::class);

    $services->set(AuthController::class)
        ->tag('controller.service_arguments');

    $services->set(ApiController::class)
        ->tag('controller.service_arguments');

    $services->set(WebhookController::class)
        ->tag('controller.service_arguments');

    $services->set(InstagramRefreshTokensCommand::class)
        ->tag('console.command');
};