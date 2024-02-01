<?php

namespace App\Service\Date;

class DateService
{
    public const YEAR = 'y';
    public const MONTH = 'm';
    public const DAY = 'd';
    public const HOUR = 'h';
    public const MINUTE = 'i';
    public const SECOND = 's';

    public const DATE_ITEMS = [self::YEAR, self::MONTH, self::DAY];
    public const TIME_ITEMS = [self::HOUR, self::MINUTE, self::SECOND];

    public const INTERVAL_ADD = 'add';
    public const INTERVAL_SUB = 'sub';

    public static function getNow(): \DateTime
    {
        return new \DateTime();
    }

    public static function getDateAccordingFutureInterval(string $baseTime, int $interval): \DateTime
    {
        return static::getDateAccordingPastOrFutureInterval(static::INTERVAL_ADD, $baseTime, $interval);
    }

    public static function getDateAccordingPastInterval(string $baseTime, int $interval): \DateTime
    {
        return static::getDateAccordingPastOrFutureInterval(static::INTERVAL_SUB, $baseTime, $interval);
    }

    public static function getPrefix(string $baseTime): string
    {
        return in_array($baseTime, static::DATE_ITEMS) ? 'P' : 'PT';
    }

    public static function getSuffix(string $baseStime): string
    {
        if (static::MINUTE === $baseStime) {
            $baseStime = static::MONTH;
        }

        return strtoupper($baseStime);
    }

    public static function isValidBaseTime(string $baseTime): bool
    {
        return preg_match('/^({static::YEAR}|{static::MONTH}|{static::DAY}|{static::HOUR}|{static::MINUTE}|{static::SECOND}|){1}$/', $baseTime);
    }

    protected static function getDateAccordingPastOrFutureInterval(string $function, string $baseTime, int $interval): \DateTime
    {
        static::isValidBaseTime($baseTime);

        $now = static::getNow();
        $date = clone $now;

        $prefix = static::getPrefix($baseTime);
        $suffix = static::getSuffix($baseTime);

        return $date->{$function}(new \DateInterval("{$prefix}{$interval}{$suffix}"));
    }
}
