<?php

namespace Chuckki\ContaoRabattBundle\Controller;

use Chuckki\ContaoHvzBundle\HvzModel;
use Chuckki\ContaoHvzBundle\HvzOrderModel;
use Chuckki\ContaoHvzBundle\HvzPlzModel;
use Chuckki\ContaoRabattBundle\Model\HvzRabattModel;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\FrontendUser;
use Contao\MemberModel;
use Contao\User;
use Doctrine\DBAL\Connection;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AjaxController extends AbstractController
{
    /**
     * @Route("/rabatt/{name}", defaults={"_scope" = "frontend", "_token_check" = false})
     */
    public function indexAction($name): Response
    {
        if (strlen($name) < 7) {
            return new Response('0');
        }
        $rabattPerzent = (int)HvzRabattModel::findRabattOnCode($name);
        if (!$rabattPerzent) {
            // todo: look how contao log
            /*
            \System::getContainer()
                ->get('monolog.logger.contao')
                ->log(LogLevel::ALERT,
                    'Falscher Rabatt-Code:'.$name,
                    array('contao' => new ContaoContext('RabattBundle compile ', TL_ERROR))
                );
            */
            return new Response('0');
        }

        return new Response($rabattPerzent);
    }

    /**
     * @Route("/search/{ort}", defaults={"_scope" = "frontend", "_token_check" = false})
     */
    public function searchAction($ort, Request $request): JsonResponse
    {
        // get country code
        $country = 'de';
        if ($request->query->has('c')) {
            $c = $request->query->get('c');
            if (strlen($c) == 2) {
                $country = $c;
            }
        }
        $ort = trim($ort);
        if ($ort !== '' && $ort !== null) {
            $isNumber = false;
            $hasNull  = false;
            $withPLZ  = false;
            //	$request = mb_strtolower($_REQUEST['ort'],'UTF-8');
            $request = htmlspecialchars($ort, ENT_QUOTES, 'UTF-8');
            $request = mb_strtolower($request, 'UTF-8');
            $plz     = null;
            $ort     = null;
            $sql     = null;
            $sql_raw = null;
            $stmt    = null;
            //////////////////////////////////////////////////////
            // CLEAN UP REQUEST
            //////////////////////////////////////////////////////
            if (!preg_match("/^([a-zA-Z0-9öäüßÖÄÜ,.() \n\r-]+)$/is", $request)) {
                //$errorArray = array('ort' => "Unerlaubte Zeichen enthalten!");
                $errorArray       = array();
                $tmpInsert        = array();
                $tmpInsert['ort'] = 'Unerlaubte Zeichen enthalten!';
                $errorArray[]     = $tmpInsert;

                return new JsonResponse($errorArray, 403);
            }
            //////////////////////////////////////////////////////
            // FIRST PLZ
            //////////////////////////////////////////////////////
            $suchmuster_plz = '/^([0]{1}|[1-9]{1})/';
            if (preg_match($suchmuster_plz, $request)) {
                $isNumber     = true;
                $splitAnfrage = explode(' ', $request);
                $requestPLZ   = $splitAnfrage[0];
                // check leading ZERO
                $suchmuster_plz = '/^([0])/';
                if (preg_match($suchmuster_plz, $requestPLZ)) {
                    $requestPLZ = substr($requestPLZ, 1);
                    $hasNull    = true;
                    // build searchSTring like "40___"
                    for ($i = strlen($requestPLZ); $i < 4; $i++) {
                        $requestPLZ .= '_';
                    }
                } else {
                    // searchString like "404%"
                    $requestPLZ .= '%';
                }
                // cut off plz
                unset($splitAnfrage[0]);
                // build up rest of string to ortString
                $requestOrt = implode(' ', $splitAnfrage);
                $requestOrt = strtolower($requestOrt).'%';
                $sql     =
                    "select distinct tl_hvz.id as hvzId, plzS as post, alias, tl_hvz.lk as lkz, question as value, isFamus from tl_hvz inner join tl_plz on tl_hvz.id = tl_plz.ortid where tl_hvz.lk like '"
                    .$country."' and tl_plz.plzS like '".$requestPLZ."' and LOWER(tl_hvz.question) like '".$requestOrt
                    ."' group by question order by isFamus DESC ,question ASC   LIMIT 0, 10";
                $sql_raw = $sql;
            }
            //////////////////////////////////////////////////////
            // FIRST ORT
            //////////////////////////////////////////////////////
            //$musterString = '/^[a-zA-Z]*/';
            $musterString = '/^[a-z-A-ZöäüßÖÄÜß()]/';
            if (preg_match($musterString, $request)) {
                $splitAnfrage = explode(' ', $request);
                // plz unknown
                $requestPLZ = '%';
                $requestOrt = '';
                foreach ($splitAnfrage as $value) {
                    // find a number as plz
                    $suchmuster_plz = '/^([0]{1}|[1-9]{1})/';
                    if (preg_match($suchmuster_plz, $value)) {
                        $withPLZ        = true;
                        $requestPLZ     = $value;
                        $suchmuster_plz = '/^([0])/';
                        if (preg_match($suchmuster_plz, $requestPLZ)) {
                            $requestPLZ = substr($requestPLZ, 1);
                            $hasNull    = true;
                            for ($i = strlen($requestPLZ); $i < 4; $i++) {
                                $requestPLZ .= '_';
                            }
                        } else {
                            $requestPLZ .= '%';
                        }
                        break;
                    }
                    $requestOrt .= $value.' ';
                }
                // cut off last whitespace
                $requestOrt = trim($requestOrt);
                $requestOrt = str_replace(array('(', ')'), array('&#40;', '&#41;'), $requestOrt);
                $requestOrt = mb_strtolower($requestOrt).'%';
                // get alternative ortStrings
                $umlaute      = array('ü', 'ö', 'ä');
                $umlautev     = array('ue', 'oe', 'ae');
                $request_alt0 = str_replace($umlautev, $umlaute, $requestOrt);
                $request_alt1 = str_replace($umlaute, $umlautev, $requestOrt);
                $request_alt2 = str_replace('ss', 'ß', $request_alt0);
                $request_alt3 = str_replace('ß', 'ss', $request_alt0);
                $request_alt4 = str_replace('ss', 'ß', $requestOrt);
                $request_alt5 = str_replace('ß', 'ss', $requestOrt);
                $sql =
                    "select distinct tl_hvz.id as hvzId, plzS as post, question as value, land, alias, isFamus, tl_hvz.lk as lkz from tl_hvz inner join tl_plz on tl_hvz.id = tl_plz.ortid where tl_hvz.lk = '"
                    .$country."' and  tl_plz.plzS like '".$requestPLZ."' and ( LOWER(question) like '".$requestOrt
                    ."' or LOWER(question) like '".$request_alt1."' or LOWER(question) like '".$request_alt2
                    ."' or LOWER(question) like '".$request_alt0."' or LOWER(question) like '".$request_alt3
                    ."' or LOWER(question) like '".$request_alt4."' or LOWER(question) like '".$request_alt5
                    ."' ) group by question order by isFamus DESC ,question ASC LIMIT 0, 5;";
                # add ausland
                $sql_raw =
                    "select distinct '' as post, tl_hvz.id as hvzId, question as value, land, alias, isFamus, tl_hvz.lk as lkz from tl_hvz where tl_hvz.lk = '"
                    .$country."' and  ( LOWER(question) like '%".$requestOrt."' or LOWER(question) like '".$requestOrt
                    ."' or LOWER(question) like '".$request_alt1."' or LOWER(question) like '".$request_alt2
                    ."' or LOWER(question) like '".$request_alt0."' or LOWER(question) like '".$request_alt3
                    ."' or LOWER(question) like '".$request_alt4."' or LOWER(question) like '".$request_alt5
                    ."' ) group by question order by isFamus DESC ,question ASC LIMIT 0, 5;";
                //echo "firstOrt:".$sql."\n\n";
            }
            $result = \Database::getInstance()->prepare($sql)->query();
            $result_raw = \Database::getInstance()->prepare($sql_raw)->query();
            $emparray     = array();
            $emparray_raw = array();
            while ($newPlz = $result_raw->fetchAssoc()) {
                $tmp = array();
                // PLZ first
                if ($isNumber) {
                    $newPlz['post'] = ($hasNull) ? '0'.$newPlz['post'] : $newPlz['post'];
                    $tmp['ort']     = $newPlz['post'].' '.$newPlz['value'];
                } else {
                    if ($withPLZ) {
                        $newPlz['post'] = ($hasNull) ? '0'.$newPlz['post'] : $newPlz['post'];
                        $tmp['ort']     = $newPlz['value'].' ('.$newPlz['post'].')';
                    } else {
                        $tmp['ort'] = $newPlz['value'];
                        //if($newPlz['land'] !== 'Deutschland'){
                        //$tmp['ort'] = $tmp['ort'].' ('.$newPlz['land'].')';
                        //}
                    }
                }
                $tmp['ort'] = str_replace(['&#40;', '&#41;'], ['(', ')'], $tmp['ort']);
                $tmp['alias']   = $newPlz['alias'];
                $tmp['id']      = $newPlz['hvzId'];
                $tmp['lkz']      = $newPlz['lkz'];
                $emparray_raw[] = $tmp;
            }
            while ($newPlz = $result->fetchAssoc()) {
                $tmp = array();
                // PLZ first
                if ($isNumber) {
                    $newPlz['post'] = ($hasNull) ? '0'.$newPlz['post'] : $newPlz['post'];
                    $tmp['ort']     = $newPlz['post'].' '.$newPlz['value'];
                } else {
                    if ($withPLZ) {
                        $newPlz['post'] = ($hasNull) ? '0'.$newPlz['post'] : $newPlz['post'];
                        $tmp['ort']     = $newPlz['value'].' ('.$newPlz['post'].')';
                    } else {
                        $tmp['ort'] = $newPlz['value'];
                    }
                }
                $tmp['ort']   = str_replace('&#40;', '(', $tmp['ort']);
                $tmp['ort']   = str_replace('&#41;', ')', $tmp['ort']);
                $tmp['alias'] = $newPlz['alias'];
                $tmp['id']    = $newPlz['hvzId'];
                $tmp['lkz']      = $newPlz['lkz'];
                $emparray[]   = $tmp;
            }
            $allErg = array_unique(array_merge($emparray, $emparray_raw), SORT_REGULAR);

            return new JsonResponse($allErg);
        }

        return new JsonResponse(null);
    }

    /**
     * @Route("/authViaToken/{token}", defaults={"_scope" = "frontend", "_token_check" = false})
     */
    public function getUserCredentials($token): JsonResponse
    {
        /** @var MemberModel $user */
        $user = MemberModel::findOneBy('token', $token);
        if (!$user || !$user instanceof MemberModel) {
             return new JsonResponse();
        }
        $response = [
            'firma'   => $user->company,
            'vorname' => $user->firstname,
            'name'    => $user->lastname,
            'strasse' => $user->street,
            'ort'     => $user->city,
            'telefon' => $user->phone,
            'gender'  => $user->gender,
            'email'   => $user->email,
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/authViaCredits", defaults={"_scope" = "frontend", "_token_check" = false}, methods={"POST"})
     */
    public function getUserCredentialsAdvanced(Request $request): JsonResponse
    {
        // check Request for uname & upw
        $data     = json_decode($request->getContent(), true);

        if(!$data['uname'] || !$data['upw']){
            return new JsonResponse(['error']);
        }
        $userName = $data['uname'];
        $userPw   = $data['upw'];
        /** @var MemberModel $user */
        $user = MemberModel::findOneBy('username', $userName);
        if (!$user || !$user instanceof MemberModel) {
            return new JsonResponse('');
        }
        $frontendUser = FrontendUser::getInstance();
        if (!$frontendUser->findBy('username', $userName)) {
            return new JsonResponse('');
        }

        if ($frontendUser && password_verify($userPw, $frontendUser->password))
        {
            $response = [
                'firma'   => $frontendUser->company,
                'vorname' => $frontendUser->firstname,
                'name'    => $frontendUser->lastname,
                'strasse' => $frontendUser->street,
                'ort'     => $frontendUser->city,
                'telefon' => $frontendUser->phone,
                'gender'  => $frontendUser->gender,
                'email'   => $frontendUser->email
            ];

            return new JsonResponse($response);
        }

        return new JsonResponse('');
    }

    /**
     * @Route("/apiCheck/", defaults={"_scope" = "frontend", "_token_check" = false})
     */
    public function listApiOrders(): JsonResponse
    {
        $ts = strtotime('-1 day');

        return $this->listApiOrdersWithTime($ts);
    }

    /**
     * @Route("/apiCheck/{ts}", defaults={"_scope" = "frontend", "_token_check" = false})
     */
    public function listApiOrdersWithTime($ts): JsonResponse
    {
        $hvzOrders = HvzOrderModel::findBy(['tstamp>?', 'orderNumber != ?'], [$ts, '0']);
        $orders    = [];
        foreach ($hvzOrders as $order) {
            $orders[] = $order->id;
        }

        return new JsonResponse($orders);
    }
}
