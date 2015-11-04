<?php

namespace Everlution\MandrillBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('everlution_mandrill');

        $rootNode
            ->children()
                ->scalarNode('api_key')
                    ->isRequired()
                    ->info('Mandrill secret API key.')
                ->end()

                ->booleanNode('async_mandrill_sending')
                    ->defaultFalse()
                    ->info('Enable a background mandrill sending mode that is optimized for bulk sending. In async mode, messages/send will immediately return a status of "queued" for every recipient.')
                ->end()

                ->scalarNode('enforced_delivery_address')
                    ->defaultValue(null)
                    ->info('Recipient address, which will be enforced for every outbound message.')
                ->end()
            ->end();

        return $treeBuilder;
    }

}
