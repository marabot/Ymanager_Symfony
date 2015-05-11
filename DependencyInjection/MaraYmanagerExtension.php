<?php
/**
 * Created by PhpStorm.
 * User: humito
 * Date: 28/04/2015
 * Time: 19:06
 */
namespace Mara\Ymanager\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class MaraYmanagerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    public function getAlias()
    {
        return 'mara_ymanager';
    }
}


