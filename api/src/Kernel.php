<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 */

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    // public const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    // public function getCacheDir(): string
    // {
    //     return $this->getProjectDir().'/var/cache/'.$this->environment;
    // }

    // public function getLogDir(): string
    // {
    //     return $this->getProjectDir().'/var/log';
    // }

    // public function registerBundles(): iterable
    // {
    //     $contents = require $this->getProjectDir().'/config/bundles.php';
    //     foreach ($contents as $class => $envs) {
    //         if (isset($envs['all']) || isset($envs[$this->environment])) {
    //             yield new $class();
    //         }
    //     }
    // }

    // protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    // {
    //     $container->addResource(new FileResource($this->getProjectDir().'/config/bundles.php'));
    //     // Feel free to remove the "container.autowiring.strict_mode" parameter
    //     // if you are using symfony/dependency-injection 4.0+ as it's the default behavior
    //     $container->setParameter('container.autowiring.strict_mode', true);
    //     $container->setParameter('container.dumper.inline_class_loader', true);
    //     $confDir = $this->getProjectDir().'/config';

    //     $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
    //     $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
    //     $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
    //     $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
    // }

    // protected function configureRoutes(RouteCollectionBuilder $routes)
    // {
    //     $confDir = $this->getProjectDir().'/config';

    //     $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
    //     $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
    //     $routes->import($confDir.'/{routes}'.self::CONFIG_EXTS, '/', 'glob');
    // }
}
