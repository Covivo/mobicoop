<?php

namespace App\Incentive\Service;

abstract class DateService
{
    public static function getExpirationDate(int $delay): \DateTimeInterface
    {
        $now = new \DateTime('now');

        return $now->add(new \DateInterval('P'.$delay.'M'));
    }
}
