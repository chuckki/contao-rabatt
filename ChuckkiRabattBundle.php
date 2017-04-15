<?php
/**
 * Created by PhpStorm.
 * User: dennisesken
 * Date: 14.04.17
 * Time: 12:45
 */

namespace Chuckki\RabattBundle;

use Chuckki\RabattBundle\DependencyInjection\RabattBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ChuckkiRabattBundle extends Bundle
{
	public function getContainerExtension()
	{
		return new RabattBundleExtension();
	}
}