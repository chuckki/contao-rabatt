<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Prori\RabattBundle\Service;

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
	}
	/**
	 * Find car by alias
	 *
	 * @param $alias
	 *
	 * @return array|\Prori\RabattBundle\Entity\Rabatt[]
	 */
	public function findByAlias($alias)
	{
		$carList = $this->entityManager->getRepository('ProriRabattBundle:Rabatt')->findBy(['alias' => $alias]);
		return $carList;
	}
	/**
	 * Find all cars
	 *
	 * @return array|\Prori\RabattBundle\Entity\Rabatt[]
	 */
	public function findAll()
	{
		$carList = $this->entityManager->getRepository('ProriRabattBundle:Rabatt')->findAll();
		return $carList;
	}
}