<?php

namespace App\Solidary\Event;

use App\Solidary\Entity\Solidary;
use Symfony\Contracts\EventDispatcher\Event;

class SolidaryMatchingEvent extends Event
{
    public const NAME = 'solidary_matching_success';

    /**
     * @var Solidary
     */
    private $_solidary;

    public function __construct(Solidary $solidary)
    {
        $this->_solidary = $solidary;
    }

    public function getSolidary(): Solidary
    {
        return $this->_solidary;
    }
}
