<?php

namespace Chuckki\ContaoRabattBundle\Controller;

use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LogLevel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{
    /**
     * @Route("/{name}", defaults={"_scope" = "frontend", "_token_check" = false})
     */
    public function indexAction($name)
    {

    	if(strlen($name) < 7){
    		$ipAddress = $_SERVER['REMOTE_ADDR'];
			if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
				$ipAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
			}
    		$this->get('logger')->critical('Rabbat Request < 7',['ip' => $ipAddress, 'request' => $name]);
			return new Response('Your request is recorded as a hacking attack. Your IP is saved and the admin is noticed. Stop it! Now!');
		}

		$objRabatt = \Database::getInstance()
            ->prepare("SELECT
                  rabattProzent,
                  rabattCode
                FROM 
                  tl_hvz_rabatt
                WHERE
                  rabattCode=? AND 
                  (start<? OR start='') AND 
                  (stop>? OR stop='')
            
            ")
            ->limit(1)
            ->execute($name,time(),time());

    	if($objRabatt->numRows < 1){
    	    \System::getContainer()
                ->get('monolog.logger.contao')
                ->log(LogLevel::ALERT,
                'Falscher Rabatt-Code:'.$name,
                    array('contao' => new ContaoContext('RabattBundle compile ', TL_ERROR))
                );
                return new Response('0');
        }


        while($objRabatt->next()){
            return new Response($objRabatt->rabattProzent);
        }

    	return new Response('0');
    }
}
