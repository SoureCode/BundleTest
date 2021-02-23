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

    protected static function clearDatabase(): void
    {
        static::runCommand(
            [
                'command' => 'doctrine:database:drop',
                '--force' => true,
            ]
        );

        static::runCommand(
            [
                'command' => 'doctrine:database:create',
            ]
        );
    }

    protected static function prepareDatabase(): void
    {
        static::runCommand(
            [
                'command' => 'doctrine:migrations:migrate',
                '--no-interaction' => true,
            ]
        );
    }
}
