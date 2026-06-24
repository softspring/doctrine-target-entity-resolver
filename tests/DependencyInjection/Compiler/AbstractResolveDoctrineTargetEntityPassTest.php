<?php

declare(strict_types=1);

namespace Softspring\Component\DoctrineTargetEntityResolver\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Softspring\Component\DoctrineTargetEntityResolver\DependencyInjection\Compiler\AbstractResolveDoctrineTargetEntityPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;

#[CoversClass(AbstractResolveDoctrineTargetEntityPass::class)]
class AbstractResolveDoctrineTargetEntityPassTest extends TestCase
{
    public function testItRegistersTargetEntityResolutionFromParameter(): void
    {
        $container = $this->createContainer();
        $container->setParameter('app.target_entity.class', TestEntity::class);

        $pass = new class extends AbstractResolveDoctrineTargetEntityPass {
            protected function getEntityManagerName(ContainerBuilder $container): string
            {
                return 'default';
            }

            public function process(ContainerBuilder $container): void
            {
                $this->setTargetEntityFromParameter('app.target_entity.class', TestEntityInterface::class, $container);
            }
        };

        $pass->process($container);

        $definition = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');

        self::assertArrayHasKey('doctrine.event_subscriber', $definition->getTags());
        self::assertSame([
            ['addResolveTargetEntity', [TestEntityInterface::class, TestEntity::class, []]],
        ], $definition->getMethodCalls());
    }

    public function testItSkipsOptionalMappingsWhenParameterIsMissing(): void
    {
        $container = $this->createContainer();

        $pass = new class extends AbstractResolveDoctrineTargetEntityPass {
            protected function getEntityManagerName(ContainerBuilder $container): string
            {
                return 'default';
            }

            public function process(ContainerBuilder $container): void
            {
                $this->setTargetEntityFromParameter('app.target_entity.class', TestEntityInterface::class, $container, false);
            }
        };

        $pass->process($container);

        $definition = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');

        self::assertSame([], $definition->getMethodCalls());
        self::assertSame([], $definition->getTags());
    }

    public function testItSkipsOptionalMappingsWhenParameterIsEmpty(): void
    {
        $container = $this->createContainer();
        $container->setParameter('app.target_entity.class', '');

        $pass = new class extends AbstractResolveDoctrineTargetEntityPass {
            protected function getEntityManagerName(ContainerBuilder $container): string
            {
                return 'default';
            }

            public function process(ContainerBuilder $container): void
            {
                $this->setTargetEntityFromParameter('app.target_entity.class', TestEntityInterface::class, $container, false);
            }
        };

        $pass->process($container);

        $definition = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');

        self::assertSame([], $definition->getMethodCalls());
        self::assertSame([], $definition->getTags());
    }

    public function testItKeepsExistingDoctrineSubscriberTag(): void
    {
        $container = $this->createContainer();
        $container->setParameter('app.target_entity.class', TestEntity::class);
        $definition = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');
        $definition->addTag('doctrine.event_subscriber', ['connection' => 'default']);

        $pass = new class extends AbstractResolveDoctrineTargetEntityPass {
            protected function getEntityManagerName(ContainerBuilder $container): string
            {
                return 'default';
            }

            public function process(ContainerBuilder $container): void
            {
                $this->setTargetEntityFromParameter('app.target_entity.class', TestEntityInterface::class, $container);
            }
        };

        $pass->process($container);

        self::assertSame([
            ['connection' => 'default'],
        ], $definition->getTag('doctrine.event_subscriber'));
        self::assertSame([
            ['addResolveTargetEntity', [TestEntityInterface::class, TestEntity::class, []]],
        ], $definition->getMethodCalls());
    }

    public function testItFailsWhenRequiredMappingIsMissing(): void
    {
        $container = $this->createContainer();

        $pass = new class extends AbstractResolveDoctrineTargetEntityPass {
            protected function getEntityManagerName(ContainerBuilder $container): string
            {
                return 'default';
            }

            public function process(ContainerBuilder $container): void
            {
                $this->setTargetEntityFromParameter('app.target_entity.class', TestEntityInterface::class, $container);
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('app.target_entity.class parameter must be a valid entity');

        $pass->process($container);
    }

    public function testItFailsWhenConfiguredClassDoesNotImplementInterface(): void
    {
        $container = $this->createContainer();
        $container->setParameter('app.target_entity.class', InvalidEntity::class);

        $pass = new class extends AbstractResolveDoctrineTargetEntityPass {
            protected function getEntityManagerName(ContainerBuilder $container): string
            {
                return 'default';
            }

            public function process(ContainerBuilder $container): void
            {
                $this->setTargetEntityFromParameter('app.target_entity.class', TestEntityInterface::class, $container);
            }
        };

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf('%s class must implements %s interface', InvalidEntity::class, TestEntityInterface::class));

        $pass->process($container);
    }

    private function createContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setDefinition('doctrine.orm.listeners.resolve_target_entity', new Definition());

        return $container;
    }
}

interface TestEntityInterface
{
}

class TestEntity implements TestEntityInterface
{
}

class InvalidEntity
{
}
