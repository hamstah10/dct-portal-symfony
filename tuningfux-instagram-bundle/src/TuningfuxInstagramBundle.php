<?php

namespace Tuningfux\InstagramBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Tuningfux\InstagramBundle\DependencyInjection\Compiler\InstagramStorePass;

class TuningfuxInstagramBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->parameters()
            ->set('tuningfux_instagram.app_id', $config['app_id'])
            ->set('tuningfux_instagram.app_secret', $config['app_secret'])
            ->set('tuningfux_instagram.redirect_uri', $config['redirect_uri'])
            ->set('tuningfux_instagram.graph_version', $config['graph_version'])
            ->set('tuningfux_instagram.webhook_verify_token', $config['webhook_verify_token'])
            ->set('tuningfux_instagram.route_prefix', $config['route_prefix'])
            ->set('tuningfux_instagram.enable_webhook', $config['enable_webhook'])
            ->set('tuningfux_instagram.enable_publish', $config['enable_publish'])
            ->set('tuningfux_instagram.enable_insights', $config['enable_insights']);
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/routes.php');
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new InstagramStorePass());
    }
}