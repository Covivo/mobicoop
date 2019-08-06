<?php
/**
 * Created by PhpStorm.
 * User: vagrant
 * Date: 8/6/19
 * Time: 11:44 AM
 */

namespace App\Carpool\Event;


class AskRefusedDriverSimpleEvent extends AskRefusedEvent
{
    public const NAME = 'driver_'.AskRefusedEvent::NAME;
}