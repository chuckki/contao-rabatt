<?php
namespace Chuckki\ContaoRabattBundle\Model;

use Contao\Model;

class HvzRabattModel extends Model
{
    protected static $strTable = 'tl_hvz_rabatt';

    public static function findRabattOnCode(string $code)
    {
        $w = "(start<? OR start='') AND (stop>? OR stop='')";
        $row =  static::findOneBy(['rabattCode = ?',$w],[$code, time(),time()]);

        if(!$row){
            return 0;
        }

        return $row->rabattProzent;
    }

}