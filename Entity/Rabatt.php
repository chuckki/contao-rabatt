<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Chuckki\RabattBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="tl_hvz_rabatt",options={"engine":"MyISAM"})
 */
class Rabatt
{
	/**
	 * @ORM\Column(type="integer", options={"unsigned"=true})
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", options={"default"=""})
	 */
	protected $rabattCode;
	/**
	 * @ORM\Column(type="string", options={"default"=""})
	 */
	protected $rabattProzent;
	/**
	 * @ORM\Column(type="text",length=11, nullable=true)
	 */
	protected $start;
	/**
	 * @ORM\Column(type="text",length=11, nullable=true)
	 */
	protected $stop;
	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $comments;
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $tstamp;
	/**
	 * @return mixed
	 */
	public function getRabattCode()
	{
		return $this->rabattCode;
	}

	/**
	 * @param mixed $rabattCode
	 */
	public function setRabattCode($rabattCode)
	{
		$this->rabattCode = $rabattCode;
	}

	/**
	 * @return mixed
	 */
	public function getRabattProzent()
	{
		return $this->rabattProzent;
	}

	/**
	 * @param mixed $rabattProzent
	 */
	public function setRabattProzent($rabattProzent)
	{
		$this->rabattProzent = $rabattProzent;
	}

	/**
	 * @return mixed
	 */
	public function getComments()
	{
		return $this->comments;
	}

	/**
	 * @param mixed $comments
	 */
	public function setComments($comments)
	{
		$this->comments = $comments;
	}

	/**
	 * @return mixed
	 */
	public function getStart()
	{
		return $this->start;
	}

	/**
	 * @param mixed $start
	 */
	public function setStart($start)
	{
		$this->start = $start;
	}

	/**
	 * @return mixed
	 */
	public function getStop()
	{
		return $this->stop;
	}

	/**
	 * @param mixed $stop
	 */
	public function setStop($stop)
	{
		$this->stop = $stop;
	}

	/**
	 * @return mixed
	 */
	public function getTstamp()
	{
		return $this->tstamp;
	}

	/**
	 * @param mixed $tstamp
	 */
	public function setTstamp($tstamp)
	{
		$this->tstamp = $tstamp;
	}


}