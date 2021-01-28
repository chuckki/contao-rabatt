<?php

namespace Chuckki\ContaoRabattBundle\Controller;

use Chuckki\ContaoHvzBundle\HvzModel;
use Chuckki\ContaoHvzBundle\HvzPlzModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    /**
     * Format PLZ;Ort;Kanton;Einseitig;Beidseitig;Ohne Gen.
     *
     * @Route("/import/{dryRun}", defaults={"_scope" = "frontend", "_token_check" = false})
     */
    public function importAction($dryRun = 1): Response
    {
        $finder      = new Finder();
        $projectRoot = $this->get('kernel')->getProjectDir();
        $finder->files()->in($projectRoot.'/import');
        foreach ($finder as $file) {
            $lines = preg_split("/\r\n/", $file->getContents());
        }
        //dump($lines);
        $newCities = [];
        $kantons   = [];
        foreach ($lines as $line) {
            $lineContent = preg_split("/;/", $line);
            if (count($lineContent) < 3) {
                continue;
            }
            if (!isset($newCities[$lineContent[1]])) {
                $newCities[$lineContent[1]] = [
                    'kanton'     => $lineContent[2],
                    'einseitig'  => $lineContent[3],
                    'beidseitig' => $lineContent[4],
                    'ohneGen'    => $lineContent[5],
                    'plz'        => [$lineContent[0]],
                ];
                $kantons[$lineContent[2]]   += 1;
            } else {
                $newCities[$lineContent[1]]['plz'][] = $lineContent[0];
            }
        }
        $plzCount = 0;
        foreach ($newCities as $key => $city) {
            $ort                = new HvzModel();
            $ort->question      = $key;
            $ort->lk            = 'ch';
            $ort->hvz_single    = $city['einseitig'];
            $ort->hvz_double    = $city['beidseitig'];
            $ort->hvz_single_og = $city['ohneGen'];
            $ort->kreis         = $city['kanton'];
            $ort->alias         = \StringUtil::generateAlias($key);
            $ort->hvz_extra_tag = 20;
            $ort->pid           = 12;
            $ort->published     = 1;
            $ort->land          = 'Schweiz';
            if ($dryRun === 'go') {
                $ort->save();
            }
            foreach ($city['plz'] as $plz) {
                $plzCount++;
                $plzObj        = new HvzPlzModel();
                $plzObj->ortid = $ort->id;
                $plzObj->plzS  = $plz;
                $plzObj->plz   = intval(trim($plz));
                if (($plzObj->plz * 2) == 0) {
                    die('no plz :'.$ort->question);
                }
                $plzObj->lk = 'ch';
                if ($dryRun === 'go') {
                    $plzObj->save();
                }
            }

        }
        $answer = 'anzahl city:'.count($newCities);
        $answer .= ' anzahl plz:'.$plzCount;
        die($answer);

        // PLZ;Ort;Kanton;Einseitig;Beidseitig;Ohne Gen.
    }

    /**
     * Format plz\tstadt\tpreis_single\tpreis_double\n
     * Source http://www.fa-technik.adfc.de/code/opengeodb/BE.tab
     *
     * @Route("/import2/{dryRun}", defaults={"_scope" = "frontend", "_token_check" = false})
     */
    public function importActionTxt($dryRun = 1): Response
    {

        $finder      = new Finder();
        $projectRoot = $this->get('kernel')->getProjectDir();
        $finder->files()->in($projectRoot.'/import');
        foreach ($finder as $file) {
            if ($file->getFilename() === "preis.txt") {
                $pricelines = preg_split("/\n/", $file->getContents());
            }
            if ($file->getFilename() === "zipcode.csv") {
                $plzLines = preg_split("/\r\n/", $file->getContents());
            }
        }

        $newCities   = [];

        //prices
        $priceList = [];
        foreach ($pricelines as $line) {
            $lineContent = preg_split("/\t/", $line);
            if (count($lineContent) !== 4) {
                echo "error: ".$line."\n";
                continue;
            }
            if (!isset($priceList[$lineContent[0]])) {
                //dump($plist);
                $priceList[$lineContent[0]] = [
                    'einseitig'  => $lineContent[2],
                    'beidseitig' => $lineContent[3],
                    'ohneGen'    => $lineContent[2] - ($lineContent[3] - $lineContent[2]),
                ];

            } else {
                $priceList[$lineContent[1]]['plz'][] = $lineContent[0];
            }
        }




        // cities + plz
        foreach ($plzLines as $line) {
            $lineContent = preg_split("/;/", $line);

            //create CityLine
            if (!isset($newCities[$lineContent[1]])) {
                $newCities[$lineContent[1]] = [
                    'einseitig'  => 0,
                    'beidseitig' => 0,
                    'ohneGen'    => 0,
                    'plz'        => [],
                ];
            };

            $plist = preg_split("/,/", $lineContent[0]);
            foreach ($plist as $item) {

                if($item === ''){
                    continue;
                }

                if(!in_array($item,$newCities[$lineContent[1]]['plz'])){
                    $newCities[$lineContent[1]]['plz'][] = $item;
                }

                if (array_key_exists($item, $priceList)) {
                    $newCities[$lineContent[1]]['einseitig']  = $priceList[$item]['einseitig'];
                    $newCities[$lineContent[1]]['beidseitig'] = $priceList[$item]['beidseitig'];
                    $newCities[$lineContent[1]]['ohneGen']    = $priceList[$item]['ohneGen'];
                }
            }

        }





        // set default Values to empty entries
        // mw_single = 255
        // mw_double = 300
        // std = 14
        foreach ($newCities as $key => $newCity) {
            if($newCity['einseitig'] === 0){
                $zuf = rand(0,200);
                $newCities[$key]['einseitig'] = (int)(255 + ($zuf / 100 * 14));
                $newCities[$key]['beidseitig'] = (int)(300 + ($zuf / 100 * 14));
                $newCities[$key]['ohneGen'] =  $newCities[$key]['einseitig'] - ($newCities[$key]['beidseitig']-$newCities[$key]['einseitig']);
            }

        }



        $lkz = 'be';
        $country = 'Belgien';
        $pid = 13;

        //dump($newCities['Antwerpen']);
        unset($newCities['']);
        $plzCount = 0;
        foreach ($newCities as $key => $city) {
            $ort                = new HvzModel();
            $ort->question      = $key;
            $ort->lk            = $lkz;
            $ort->hvz_single    = $city['einseitig'];
            $ort->hvz_double    = $city['beidseitig'];
            $ort->hvz_single_og = $city['ohneGen'];
            $ort->kreis         = "";
            $ort->alias         = \StringUtil::generateAlias($key);
            $ort->hvz_extra_tag = 25;
            $ort->pid           = $pid;
            $ort->published     = 1;
            $ort->land          = $country;
            if ($dryRun === 'go') {
                $ort->save();
            }
            foreach ($city['plz'] as $plz) {
                $plzCount++;
                $plzObj        = new HvzPlzModel();
                $plzObj->ortid = $ort->id;
                $plzObj->plzS  = $plz;
                $plzObj->plz   = intval(trim($plz));
                if (($plzObj->plz * 2) == 0) {
                    continue;
                    die('no plz :'.$ort->question);
                }
                $plzObj->lk = $lkz;
                if ($dryRun === 'go') {
                    $plzObj->save();
                }
            }

        }
        $answer = 'anzahl city:'.count($newCities);
        $answer .= ' anzahl plz:'.$plzCount;
        die($answer);

    }


}