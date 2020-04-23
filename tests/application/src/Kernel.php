<?php

declare(strict_types=1);

namespace Netgen\Layouts\Tests\App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use function dirname;
use function getenv;
use function is_string;
use function mb_stripos;
use function putenv;
use function sys_get_temp_dir;
use function trim;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir() . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    public function boot(): void
    {
        parent::boot();

        $databaseUrl = getenv('DATABASE');
        if (!is_string($databaseUrl) || trim($databaseUrl) === '') {
            $databaseUrl = 'sqlite:///' . $this->getCacheDir() . '/nglayouts.db';
        }

        putenv('DATABASE=' . $databaseUrl);

        $databaseCharset = mb_stripos($databaseUrl, 'mysql://') === 0 ? 'utf8mb4' : 'utf8';
        putenv('DATABASE_CHARSET=' . $databaseCharset);
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/nglayouts/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/nglayouts/logs';
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        // Temp fix for https://github.com/doctrine/DoctrineMigrationsBundle/issues/305
        $container->setParameter('kernel.root_dir', $container->getParameter('kernel.project_dir') . '/config');

        $container->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir() . '/config';

        $loader->load($confDir . '/{packages}/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{packages}/netgen_layouts/**/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}_' . $this->environment . self::CONFIG_EXTS, 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir() . '/config';

        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS, '/', 'glob');
    }

    protected function getContainerBaseClass(): string
    {
        return '\\' . MockerContainer::class;
    }
}
