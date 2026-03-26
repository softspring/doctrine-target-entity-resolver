# Doctrine Target Entity Resolver Features

Functional definition for `softspring/doctrine-target-entity-resolver`.

This file defines the expected behavior and scope of the component.

## Purpose

- Help reusable Symfony bundles register Doctrine target entity resolution from package configuration.
- Bridge package-level interfaces and application-level concrete entity classes.
- Keep target entity wiring in compiler passes instead of duplicating low-level listener configuration in every bundle.

## Main Features

- Provide an abstract compiler pass base class for target entity resolution.
- Resolve entity classes from container parameters.
- Validate that configured classes implement the expected interface.
- Register target entity mappings in Doctrine's resolve target entity listener.
- Support required and optional target entity mappings.

## Expected Usage

- Extend `AbstractResolveDoctrineTargetEntityPass` inside a bundle or component.
- Read target entity class names from bundle parameters.
- Call `setTargetEntityFromParameter()` for each interface-to-entity mapping.
- Use it in bundles that ship interfaces or abstract model contracts but expect the application to provide the concrete Doctrine entities.

## Operational Expectations

- Required mappings should fail fast during container compilation when the parameter is missing.
- Invalid configured classes should fail during compilation instead of producing a partially wired application.
- Optional mappings should be skipped cleanly when no parameter is configured.
- The component should remain small and focused on target entity registration, not on full Doctrine configuration.

## Current Limits

- The package is designed for Symfony dependency injection compilation, not for runtime entity discovery.
- It assumes Doctrine resolve target entity support is available in the consuming application.
- It only helps with interface-to-class registration; mapping files and entity metadata remain the responsibility of the consuming bundle and application.
