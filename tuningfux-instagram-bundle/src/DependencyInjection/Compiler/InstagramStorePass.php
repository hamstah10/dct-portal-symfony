<?php

declare(strict_types=1);

namespace Tuningfux\InstagramBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tuningfux\InstagramBundle\Store\InstagramAccountStoreInterface;

final class InstagramStorePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter('tuningfux_instagram.store_service')) {
            return;
        }

        $storeService = $container->getParameter('tuningfux_instagram.store_service');

        if (!is_string($storeService) || $storeService === '') {
            return;
        }

        if ($container->has($storeService)) {
            $container->setAlias(InstagramAccountStoreInterface::class, $storeService);
        }
    }
}
