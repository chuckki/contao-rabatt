<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Chuckki\RabattBundle\Service;

use Doctrine\ORM\EntityManager;
/**
 * Class CarService
 */
class RabattService
{
	/**
	 * @var EntityManager
	 */
	private $entityManager;
	/**
	 * CarService constructor.
	 *
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
		die('call the rabatt service');
	}
}