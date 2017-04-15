<?php

namespace Prori\RabattBundle\Controller;

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
    	$rabatte = $this->getDoctrine()->getRepository('ProriRabattBundle:Rabatt')->findAll();

    	$brand = "nix da";
		foreach ($rabatte as $item) {
			$brand = $item->getBrand();
			$test = $item->getBrand();
    	}

    	return new Response($brand);
    }
}
