<?php

namespace App\DependencyInjection\Compiler;

use App\Service\ResponseBuilderChain;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ResponseBuilderPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        if (!$container->has(ResponseBuilderChain::class)) {
            return;
        }

        $definition = $container->findDefinition(ResponseBuilderChain::class);
        $taggedServices = $container->findTaggedServiceIds('app.response_builder');
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addBuilder', [new Reference($id)]);
        }
    }
}
