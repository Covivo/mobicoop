<?php
/**
 * Created by PhpStorm.
 * User: vagrant
 * Date: 8/6/19
 * Time: 11:44 AM
 */

namespace App\Carpool\Event;


class AskAcceptedPassengerRegularEvent extends AskAcceptedEvent
{
    public const NAME = 'passenger_'.AskAcceptedEvent::NAME.'_regular';
}