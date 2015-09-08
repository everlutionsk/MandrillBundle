<?php

namespace Everlution\MandrillBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class MailSystemCompilerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerOutboundRawMessageTransformers($container, $container->getDefinition('everlution.mandrill.mail_system'));
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition $mailSystemServiceDefinition
     */
    protected function registerOutboundRawMessageTransformers(ContainerBuilder $container, Definition $mailSystemServiceDefinition)
    {
        $transformerTag = 'everlution.mandrill.outbound.raw_message_transformer';

        foreach ($container->findTaggedServiceIds($transformerTag) as $id => $attributes) {
            $mailSystemServiceDefinition->addMethodCall('addRawMessageTransformer', array(new Reference($id)));
        }
    }

}
