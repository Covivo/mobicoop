<?php
namespace Mobicoop\Bundle\MobicoopBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Tools Box
 * @author Maxime Bardot <maxime.bardot@covivo.eu>
 */
class UtilsService extends AbstractController
{
    /**
     * Convert time given in seconds to a human readable format
     * hours minutes seconds
     * @param int $time : time in seconds
     * @return string
     */
    public static function convertSecondsToHumain($time)
    {
        $hours = floor($time / 3600);
        $minutes = floor(($time / 60) % 60);
        $seconds = $time % 60;

        $humanReturn = "";
        if($hours != 0){
            $humanReturn .= $hours." h ";
        }

        $humanReturn .= $minutes." m";

        if($seconds != 0){
            $humanReturn .= " ".$seconds." s";
        }


        return $humanReturn;
    }


}