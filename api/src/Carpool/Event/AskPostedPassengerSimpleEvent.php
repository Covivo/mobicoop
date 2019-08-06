<?php
/**
 * Created by PhpStorm.
 * User: vagrant
 * Date: 8/6/19
 * Time: 11:44 AM
 */

namespace App\Carpool\Event;


class AskPostedPassengerSimpleEvent extends AskPostedEvent
{
    public const NAME = 'passenger_'.AskPostedEvent::NAME;
}