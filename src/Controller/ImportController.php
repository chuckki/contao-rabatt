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
}