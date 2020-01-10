<?php

/**
 * @copyright  Dennis Esken 2017 <http://projektorientiert.de>
 * @author     Dennis Esken (Chuckki)
 * @package    Contao-Hvz
 * @license    LGPL-3.0+
 * @see	       https://github.com/chuckki/contao-hvz
 *
 */

namespace Chuckki\ContaoRabattBundle\ContaoManager;

use Chuckki\ContaoRabattBundle\ChuckkiContaoRabattBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ChuckkiContaoRabattBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setReplace(['hvz']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        return $resolver
            ->resolve(__DIR__.'/../Resources/config/routing.yml')
            ->load(__DIR__.'/../Resources/config/routing.yml')
            ;
    }

}