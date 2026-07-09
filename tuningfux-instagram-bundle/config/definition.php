<?php

declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    $definition->rootNode()
        ->children()
            ->scalarNode('app_id')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('app_secret')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('redirect_uri')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('graph_version')->defaultValue('v25.0')->end()
            ->scalarNode('webhook_verify_token')->defaultValue('change_me')->end()
            ->scalarNode('route_prefix')->defaultValue('/instagram')->end()
            ->booleanNode('enable_webhook')->defaultTrue()->end()
            ->booleanNode('enable_publish')->defaultTrue()->end()
            ->booleanNode('enable_insights')->defaultTrue()->end()
            ->scalarNode('store_service')->defaultNull()->end()
        ->end();
};
