<?php

namespace UVDesk\CommunityPackages\UVDesk\ECommerce\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class PackageConfiguration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('uvdesk_ecommerce')
            ->children()
                ->node('shopify', 'array')
                    ->children()
                        ->node('channels', 'array')
                            ->arrayPrototype()
                                ->children()
                                    ->node('id', 'scalar')->cannotBeEmpty()->end()
                                    ->node('domain', 'scalar')->cannotBeEmpty()->end()
                                    ->node('name', 'scalar')->cannotBeEmpty()->end()
                                    ->node('client', 'scalar')->cannotBeEmpty()->end()
                                    ->node('password', 'scalar')->cannotBeEmpty()->end()
                                    ->node('enabled', 'boolean')->defaultFalse()->end()
                                    ->node('timezone', 'scalar')->cannotBeEmpty()->end()
                                    ->node('iana_timezone', 'scalar')->cannotBeEmpty()->end()
                                    ->node('currency_format', 'scalar')->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->node('bigcommerce', 'array')
                    ->children()
                        ->node('channels', 'array')
                            ->arrayPrototype()
                                ->children()
                                    ->node('id', 'scalar')->cannotBeEmpty()->end()
                                    ->node('domain', 'scalar')->cannotBeEmpty()->end()
                                    ->node('store_hash', 'scalar')->cannotBeEmpty()->end()
                                    ->node('api_token', 'scalar')->cannotBeEmpty()->end()
                                    ->node('api_client_id', 'scalar')->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->node('magento', 'array')
                    ->children()
                        ->node('channels', 'array')
                            ->arrayPrototype()
                                ->children()
                                    ->node('id', 'scalar')->cannotBeEmpty()->end()
                                    ->node('domain', 'scalar')->cannotBeEmpty()->end()
                                    ->node('api_username', 'scalar')->cannotBeEmpty()->end()
                                    ->node('api_password', 'scalar')->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->node('opencart', 'array')
                    ->children()
                        ->node('channels', 'array')
                            ->arrayPrototype()
                                ->children()
                                    ->node('id', 'scalar')->cannotBeEmpty()->end()
                                    ->node('domain', 'scalar')->cannotBeEmpty()->end()
                                    ->node('api_key', 'scalar')->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
