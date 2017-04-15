<?php
/**
 * Created by PhpStorm.
 * User: dennisesken
 * Date: 14.04.17
 * Time: 12:45
 */

namespace Prori\RabattBundle;

use Prori\RabattBundle\DependencyInjection\RabattBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ProriRabattBundle extends Bundle
{
	public function getContainerExtension()
	{
		return new RabattBundleExtension();
	}
}