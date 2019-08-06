<?php
/**
 * Created by PhpStorm.
 * User: vagrant
 * Date: 8/6/19
 * Time: 11:44 AM
 */

namespace App\Carpool\Event;


class AskAcceptedDriverRegularEvent extends AskAcceptedEvent
{
    public const NAME = 'driver_'.AskAcceptedEvent::NAME.'_regular';
}