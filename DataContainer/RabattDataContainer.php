<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Prori\RabattBundle\DataContainer;

use Doctrine\ORM\EntityManager;

/**
 * Class CarDataContainer
 * @package Xuad\CarBundle\DataContainer
 */
class RabattDataContainer
{
	/**
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 * Constructor.
	 *
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}

}