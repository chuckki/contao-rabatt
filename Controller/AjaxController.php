<?php

namespace Chuckki\RabattBundle\Controller;

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
    	$rabatt = $this->getDoctrine()->getRepository('ChuckkiRabattBundle:Rabatt')
			->findOneBy(['rabattCode' => $name]);

    	if($rabatt){
    		return new Response($rabatt->getRabattProzent());
		}

    	return new Response('0');
    }
}
