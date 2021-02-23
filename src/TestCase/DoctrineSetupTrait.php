<?php

namespace SoureCode\BundleTest\TestCase;

trait DoctrineSetupTrait
{
    protected function setupDoctrine(): void
    {
        $configurator = static::getKernelConfigurator();

        $mappings = $this->getDoctrineMappings();
        $migrations = $this->getDoctrineMigrations();

        $configurator->setDoctrine($mappings);
        $configurator->setDoctrineMigrations($migrations);
    }

    abstract protected function getDoctrineMappings(): array;

    abstract protected function getDoctrineMigrations(): array;

    protected function clearDatabase(): void
    {
        $this->runCommand(
            [
                'command' => 'doctrine:database:drop',
                '--force' => true,
            ]
        );

        $this->runCommand(
            [
                'command' => 'doctrine:database:create',
            ]
        );
    }

    protected function prepareDatabase(): void
    {
        $this->runCommand(
            [
                'command' => 'doctrine:migrations:migrate',
                '--no-interaction' => true,
            ]
        );
    }
}
