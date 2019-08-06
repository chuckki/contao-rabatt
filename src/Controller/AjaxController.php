<?php

namespace Chuckki\ContaoRabattBundle\Controller;

use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LogLevel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{
    /**
     * @Route("/rabatt/{name}", defaults={"_scope" = "frontend", "_token_check" = false})
     */
    public function indexAction($name)
    {

    	if(strlen($name) < 7){
            return new Response('0');
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

    /**
     * @Route("/search/{ort}", defaults={"_scope" = "frontend", "_token_check" = false})
     */
    public function searchAction($ort){
        $ort = trim($ort);
        if($ort != "" and $ort != null){
            $isNumber = false;
            $hasNull = false;
            $withPLZ = false;
            //	$request = mb_strtolower($_REQUEST['ort'],'UTF-8');
            $request = htmlspecialchars($ort, ENT_QUOTES, 'UTF-8');
            $request = mb_strtolower($request,'UTF-8');
            $plz = NULL;
            $ort = NULL;
            $sql = NULL;
            $sql_raw = NULL;
            $stmt = NULL;

            //////////////////////////////////////////////////////
            // CLEAN UP REQUEST
            //////////////////////////////////////////////////////

            if (!preg_match("/^([a-zA-Z0-9öäüßÖÄÜß,.() \n\r-]+)$/is", $request)) {
                //$errorArray = array('ort' => "Unerlaubte Zeichen enthalten!");
                $errorArray = array();
                $tmpInsert  = array();
                $tmpInsert['ort'] = "Unerlaubte Zeichen enthalten!";
                $errorArray[] = $tmpInsert;

                return new JsonResponse($errorArray, 403);
            }

            //////////////////////////////////////////////////////
            // FIRST PLZ
            //////////////////////////////////////////////////////

            $suchmuster_plz = '/^([0]{1}|[1-9]{1})/';
            if(preg_match($suchmuster_plz, $request)){
                $isNumber = true;
                $splitAnfrage = explode(' ',$request);
                $requestPLZ = $splitAnfrage[0];
                // check leading ZERO
                $suchmuster_plz = '/^([0])/';
                if(preg_match($suchmuster_plz, $requestPLZ)){
                    $requestPLZ = substr($requestPLZ, 1);
                    $hasNull = true;
                    // build searchSTring like "40___"
                    for( $i=strlen($requestPLZ);$i<4;$i++){
                        $requestPLZ .= '_';
                    }
                }else{
                    // searchString like "404%"
                    $requestPLZ .= '%';
                }

                // cut off plz
                unset($splitAnfrage[0]);
                // build up rest of string to ortString
                $requestOrt = implode(' ',$splitAnfrage);
                $requestOrt = strtolower($requestOrt)."%";

                $sql = "select distinct tl_hvz.id as hvzId, plzS as post, alias, question as value, isFamus from tl_hvz inner join tl_plz on tl_hvz.id = tl_plz.ortid where tl_plz.plzS like '".$requestPLZ."' and LOWER(tl_hvz.question) like '".$requestOrt."' group by question order by isFamus DESC ,question ASC   LIMIT 0, 10";
                $sql_raw = $sql;
            }

            //////////////////////////////////////////////////////
            // FIRST ORT
            //////////////////////////////////////////////////////

            //$musterString = '/^[a-zA-Z]*/';
            $musterString = '/^[a-z-A-ZöäüßÖÄÜß]/';
            if(preg_match($musterString, $request)){
                $splitAnfrage = explode(' ',$request);
                // plz unknown
                $requestPLZ = '%';
                $requestOrt = '';
                foreach ($splitAnfrage as $value) {
                    // find a number as plz
                    $suchmuster_plz = '/^([0]{1}|[1-9]{1})/';
                    if(preg_match($suchmuster_plz, $value)){
                        $withPLZ = true;
                        $requestPLZ = $value;
                        $suchmuster_plz = '/^([0])/';
                        if(preg_match($suchmuster_plz, $requestPLZ)){
                            $requestPLZ = substr($requestPLZ, 1);
                            $hasNull = true;
                            for( $i=strlen($requestPLZ);$i<4;$i++){
                                $requestPLZ .= '_';
                            }
                        }else{
                            $requestPLZ .= '%';
                        }
                        break;
                    }
                    $requestOrt .= $value." ";
                }
                // cut off last whitespace
                $requestOrt = trim($requestOrt);
                $requestOrt = mb_strtolower($requestOrt)."%";


                // get alternative ortStrings
                $umlaute  = array("ü","ö","ä");
                $umlautev = array("ue","oe","ae");
                $request_alt0 = str_replace($umlautev, $umlaute, $requestOrt);
                $request_alt1 = str_replace($umlaute, $umlautev, $requestOrt);

                $request_alt2 = str_replace('ss', 'ß', $request_alt0);
                $request_alt3 = str_replace('ß', 'ss', $request_alt0);

                $request_alt4 = str_replace('ss', 'ß', $requestOrt);
                $request_alt5 = str_replace('ß', 'ss', $requestOrt);

                $sql = "select distinct tl_hvz.id as hvzId, plzS as post, question as value, land, alias, isFamus from tl_hvz inner join tl_plz on tl_hvz.id = tl_plz.ortid where tl_plz.plzS like '".$requestPLZ."' and ( LOWER(question) like '".$requestOrt."' or LOWER(question) like '".$request_alt1."' or LOWER(question) like '".$request_alt2."' or LOWER(question) like '".$request_alt0."' or LOWER(question) like '".$request_alt3."' or LOWER(question) like '".$request_alt4."' or LOWER(question) like '".$request_alt5."' ) group by question order by isFamus DESC ,question ASC LIMIT 0, 5;";

                # add ausland
                //$sql_raw = "select distinct question as value, land, isFamus from tl_hvz where ( LOWER(question) like '".$requestOrt."' or LOWER(question) like '".$request_alt1."' or LOWER(question) like '".$request_alt2."' or LOWER(question) like '".$request_alt0."' or LOWER(question) like '".$request_alt3."' or LOWER(question) like '".$request_alt4."' or LOWER(question) like '".$request_alt5."' ) group by question order by isFamus DESC ,question ASC LIMIT 0, 5;";
                $sql_raw = "select distinct '' as post, tl_hvz.id as hvzId, question as value, land, alias, isFamus from tl_hvz where ( LOWER(question) like '%".$requestOrt."' or LOWER(question) like '".$requestOrt."' or LOWER(question) like '".$request_alt1."' or LOWER(question) like '".$request_alt2."' or LOWER(question) like '".$request_alt0."' or LOWER(question) like '".$request_alt3."' or LOWER(question) like '".$request_alt4."' or LOWER(question) like '".$request_alt5."' ) group by question order by isFamus DESC ,question ASC LIMIT 0, 5;";
                //echo "firstOrt:".$sql."\n\n";

            }


            $result = \Database::getInstance()
                ->prepare($sql)
                ->query();

            $result_raw = \Database::getInstance()
                ->prepare($sql_raw)
                ->query();

            $emparray = array();
            $emparray_raw = array();

            $justValue = array();
            //$db->close();

            while($newPlz = $result_raw->fetchAssoc()){
                $tmp = array();
                // PLZ first
                if($isNumber){
                    $newPlz['post'] = ($hasNull) ? "0".$newPlz['post']: $newPlz['post'];
                    $tmp['ort'] = $newPlz['post']." ".$newPlz['value'];
                    $justValue[] = $newPlz['post']." ".$newPlz['value'];
                }else{
                    if($withPLZ){
                        $newPlz['post'] = ($hasNull) ? "0".$newPlz['post']: $newPlz['post'];
                        $tmp['ort'] = $newPlz['value']." (".$newPlz['post'].")";
                        $justValue[] = $newPlz['value']." (".$newPlz['post'].")";
                    }else{
                        $tmp['ort'] = $newPlz['value'];
                        if ($newPlz['land'] != 'Deutschland'){
                            //$tmp['ort'] = $tmp['ort'].' ('.$newPlz['land'].')';
                        }
                        $justValue[] = $newPlz['value'];
                    }
                }
                $tmp['ort'] = str_replace('&#40;', '(', $tmp['ort']);
                $tmp['ort'] = str_replace('&#41;', ')', $tmp['ort']);
                $tmp['alias'] = $newPlz['alias'];
                $tmp['id'] = $newPlz['hvzId'];
                $emparray_raw[] = $tmp;
            }


            while($newPlz = $result->fetchAssoc()){
                $tmp = array();
                // PLZ first
                if($isNumber){
                    $newPlz['post'] = ($hasNull) ? "0".$newPlz['post']: $newPlz['post'];
                    $tmp['ort'] = $newPlz['post']." ".$newPlz['value'];
                    $justValue[] = $newPlz['post']." ".$newPlz['value'];
                }else{
                    if($withPLZ){
                        $newPlz['post'] = ($hasNull) ? "0".$newPlz['post']: $newPlz['post'];
                        $tmp['ort'] = $newPlz['value']." (".$newPlz['post'].")";
                        $justValue[] = $newPlz['value']." (".$newPlz['post'].")";
                    }else{
                        $tmp['ort'] = $newPlz['value'];
                        $justValue[] = $newPlz['value'];
                    }
                }
                $tmp['ort'] = str_replace('&#40;', '(', $tmp['ort']);
                $tmp['ort'] = str_replace('&#41;', ')', $tmp['ort']);
                $tmp['alias'] = $newPlz['alias'];
                $tmp['id'] = $newPlz['hvzId'];
                $emparray[] = $tmp;
            }

            $allErg = array_unique(array_merge($emparray,$emparray_raw), SORT_REGULAR);

            return new JsonResponse($allErg);
        }else{
            return new JsonResponse(null);
        }
    }

    /**
     * @Route("/apiCheck/", defaults={"_scope" = "frontend", "_token_check" = false})
     */
    public function listApiOrders(){

        $ts = strtotime("-1 day");
        return $this->listApiOrdersWithTime($ts);
    }

    /**
     * @Route("/apiCheck/{ts}", defaults={"_scope" = "frontend", "_token_check" = false})
     */
    public function listApiOrdersWithTime($ts){

        $objRabatt = \Database::getInstance()
            ->prepare("SELECT
                  orderNumber
                FROM 
                  tl_hvz_orders
                WHERE
                  tstamp>? AND orderNumber != '0'
            ")
            ->execute($ts);

        $orderNums = array();
        while($objRabatt->next()){
            $orderNums[] = $objRabatt->orderNumber;
        }

        return new JsonResponse($orderNums);
    }


}
