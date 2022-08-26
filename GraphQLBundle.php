<?php

namespace Youshido\GraphQLBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Youshido\GraphQLBundle\DependencyInjection\Compiler\GraphQlCompilerPass;
use Youshido\GraphQLBundle\DependencyInjection\GraphQLExtension;

class GraphQLBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GraphQlCompilerPass());
    }


    public function getContainerExtension(): GraphQLExtension
    {
        if (null === $this->extension) {
            $this->extension = new GraphQLExtension();
        }

        return $this->extension;
    }

}
