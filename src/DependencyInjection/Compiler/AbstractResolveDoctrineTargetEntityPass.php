<?php

namespace Softspring\Component\DoctrineTargetEntityResolver\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;

abstract class AbstractResolveDoctrineTargetEntityPass implements CompilerPassInterface
{
    abstract protected function getEntityManagerName(ContainerBuilder $container): string;

    protected function setTargetEntityFromParameter(string $parameterName, string $interface, ContainerBuilder $container, bool $required = true)
    {
        if ($container->hasParameter($parameterName) && $class = $container->getParameter($parameterName)) {
            $refClass = new \ReflectionClass($class);

            if (!$refClass->implementsInterface($interface)) {
                throw new LogicException(sprintf('%s class must implements %s interface', $class, $interface));
            }

            $this->setTargetEntity($container, $interface, $class);
        } else {
            if ($required) {
                throw new InvalidArgumentException(sprintf('%s parameter must be a valid entity', $parameterName));
            }
        }
    }

    private function setTargetEntity(ContainerBuilder $container, string $interface, string $class)
    {
        $resolveTargetEntityListener = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');

        if (!$resolveTargetEntityListener->hasTag('doctrine.event_subscriber')) {
            $resolveTargetEntityListener->addTag('doctrine.event_subscriber');
        }

        $resolveTargetEntityListener->addMethodCall('addResolveTargetEntity', [$interface, $class, [$this->getEntityManagerName($container)]]);
    }
}
