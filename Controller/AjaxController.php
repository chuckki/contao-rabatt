<?php

namespace Chuckki\RabattBundle\Controller;

use Doctrine\ORM\Tools\Console\Command\InfoCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{
    /**
     * @Route("/{name}")
     */
    public function indexAction($name)
    {
    	if(sizeof($name) < 7){
    		$ipAddress = $_SERVER['REMOTE_ADDR'];
			if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
				$ipAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
			}
    		$this->get('logger')->critical('Rabbat Request < 7',['ip' => $ipAddress, 'request' => $name]);
			return new Response('Your request is recorded as a hacking attack. Your IP is saved and the admin is noticed. Stop it! Now!');
		}

    	$rabatt = $this->getDoctrine()->getRepository('ChuckkiRabattBundle:Rabatt')
			->findOneBy(['rabattCode' => $name]);

    	if($rabatt){
    		return new Response($rabatt->getRabattProzent());
		}

    	return new Response('0');
    }
}
