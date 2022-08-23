<?php

namespace Youshido\GraphQLBundle\Tests\DependencyInjection;


use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Youshido\GraphQLBundle\DependencyInjection\GraphQLExtension;

class GraphQLExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConfigIsUsed()
    {
        $container = $this->loadContainerFromFile('empty', 'yml');

        static::assertNull($container->getParameter('graphql.schema_class'));
        static::assertEquals(null, $container->getParameter('graphql.max_complexity'));
        static::assertEquals(null, $container->getParameter('graphql.logger'));
        static::assertEmpty($container->getParameter('graphql.security.white_list'));
        static::assertEmpty($container->getParameter('graphql.security.black_list'));
        static::assertEquals([
            'field' => false,
            'operation' => false,
        ], $container->getParameter('graphql.security.guard_config'));

        static::assertTrue($container->getParameter('graphql.response.json_pretty'));
        static::assertEquals([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Headers' => 'Content-Type',
            ], $container->getParameter('graphql.response.headers'));
    }

    public function testDefaultCanBeOverridden()
    {
        $container = $this->loadContainerFromFile('full', 'yml');
        static::assertEquals('AppBundle\GraphQL\Schema', $container->getParameter('graphql.schema_class'));
        static::assertEquals(10, $container->getParameter('graphql.max_complexity'));
        static::assertEquals('@logger', $container->getParameter('graphql.logger'));

        static::assertEquals(['hello'], $container->getParameter('graphql.security.black_list'));
        static::assertEquals(['world'], $container->getParameter('graphql.security.white_list'));
        static::assertEquals([
            'field' => true,
            'operation' => true,
        ], $container->getParameter('graphql.security.guard_config'));

        static::assertFalse($container->getParameter('graphql.response.json_pretty'));
        static::assertEquals([
            'X-Powered-By' => 'GraphQL',
        ], $container->getParameter('graphql.response.headers'));

    }

    private function loadContainerFromFile($file, $type, array $services = [], $skipEnvVars = false)
    {
        $container = new ContainerBuilder();
        if ($skipEnvVars && !method_exists($container, 'resolveEnvPlaceholders')) {
            static::markTestSkipped('Runtime environment variables has been introduced in the Dependency Injection version 3.2.');
        }
        $container->setParameter('kernel.debug', false);
        $container->setParameter('kernel.cache_dir', '/tmp');
        foreach ($services as $id => $service) {
            $container->set($id, $service);
        }
        $container->registerExtension(new GraphQLExtension());
        $locator = new FileLocator(__DIR__.'/Fixtures/config/'.$type);

        $loader = match ($type) {
            'xml' => new XmlFileLoader($container, $locator),
            'yml' => new YamlFileLoader($container, $locator),
            'php' => new PhpFileLoader($container, $locator),
            default => throw new \InvalidArgumentException('Invalid file type'),
        };

        $loader->load($file.'.'.$type);
        $container->getCompilerPassConfig()->setOptimizationPasses([new ResolveDefinitionTemplatesPass()]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();
        return $container;
    }
}